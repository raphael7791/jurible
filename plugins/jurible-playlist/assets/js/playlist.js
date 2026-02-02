/**
 * Jurible Playlist JavaScript
 * Player control and progress tracking
 */

(function($) {
    'use strict';

    const JuriblePlaylist = {
        player: null,
        playerFrame: null,
        currentVideoId: null,
        collectionId: null,
        autoplay: true,
        videos: [],
        currentIndex: 0,
        progressData: {},
        trackingInterval: null,
        videoStartTime: 0,

        init: function() {
            const $playlist = $('.jurible-playlist');
            if (!$playlist.length) return;

            this.collectionId = $playlist.data('collection');
            this.playerFrame = document.getElementById('jurible-player');
            this.videos = $playlist.find('.jurible-video-item').toArray();
            this.autoplay = $('#jurible-autoplay').is(':checked');

            // Get first video ID
            if (this.videos.length > 0) {
                this.currentVideoId = $(this.videos[0]).data('video-id');
                this.currentIndex = 0;
            }

            this.bindEvents();
            this.loadProgress();
            this.initPlayerMessaging();
        },

        bindEvents: function() {
            const self = this;

            // Video item click
            $('.jurible-video-item').on('click', function() {
                const $item = $(this);
                const videoId = $item.data('video-id');
                const index = $item.data('index');

                self.playVideo(videoId, index);
            });

            // Autoplay toggle
            $('#jurible-autoplay').on('change', function() {
                self.autoplay = $(this).is(':checked');
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if (!$('.jurible-playlist').length) return;

                // Only if not typing in an input
                if ($(e.target).is('input, textarea')) return;

                switch(e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        self.playPrevious();
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        self.playNext();
                        break;
                }
            });
        },

        initPlayerMessaging: function() {
            const self = this;

            // Listen for messages from iframe
            window.addEventListener('message', function(event) {
                // Verify origin for security
                if (!event.origin.includes('mediadelivery.net') && !event.origin.includes('bunnycdn')) {
                    return;
                }

                try {
                    let data = event.data;
                    if (typeof data === 'string') {
                        data = JSON.parse(data);
                    }

                    self.handlePlayerMessage(data);
                } catch (e) {
                    // Not a JSON message, ignore
                }
            });

            // Start tracking interval
            this.startTracking();
        },

        handlePlayerMessage: function(data) {
            const self = this;

            // Handle different message types from Bunny Player
            if (data.event === 'videoProgress' || data.type === 'progress') {
                const percent = data.percent || data.progress || 0;

                // Mark as completed at 90%
                if (percent >= 0.9) {
                    self.markCompleted(self.currentVideoId);
                }
            }

            if (data.event === 'videoEnded' || data.type === 'ended') {
                self.markCompleted(self.currentVideoId);

                // Auto-play next video
                if (self.autoplay) {
                    setTimeout(function() {
                        self.playNext();
                    }, 1000);
                }
            }

            // Handle play event
            if (data.event === 'videoPlay' || data.type === 'play') {
                self.markStarted(self.currentVideoId);
            }
        },

        startTracking: function() {
            const self = this;

            // Clear existing interval
            if (this.trackingInterval) {
                clearInterval(this.trackingInterval);
            }

            // Track watch time every 30 seconds
            this.trackingInterval = setInterval(function() {
                if (self.currentVideoId && juriblePlaylist.isLoggedIn) {
                    self.updateWatchTime();
                }
            }, 30000);
        },

        playVideo: function(videoId, index) {
            const self = this;

            // Update UI
            $('.jurible-video-item').removeClass('active');
            $('.jurible-video-item').find('.jurible-now-playing').remove();

            const $item = $(`.jurible-video-item[data-video-id="${videoId}"]`);
            $item.addClass('active');

            // Add now playing indicator
            $item.find('.jurible-video-thumbnail').append(`
                <div class="jurible-now-playing">
                    <span></span><span></span><span></span>
                </div>
            `);

            // Update title
            const title = $item.find('.jurible-video-title').text();
            $('.jurible-current-title').text(title);

            // Update player iframe
            const embedUrl = juriblePlaylist.pullZoneUrl + videoId + '?autoplay=true&preload=true';
            this.playerFrame.src = embedUrl;

            // Update state
            this.currentVideoId = videoId;
            this.currentIndex = index;
            this.videoStartTime = Date.now();

            // Mark as started
            this.markStarted(videoId);

            // Scroll to active item
            this.scrollToActive();
        },

        playNext: function() {
            if (this.currentIndex < this.videos.length - 1) {
                const nextIndex = this.currentIndex + 1;
                const $nextItem = $(this.videos[nextIndex]);
                this.playVideo($nextItem.data('video-id'), nextIndex);
            }
        },

        playPrevious: function() {
            if (this.currentIndex > 0) {
                const prevIndex = this.currentIndex - 1;
                const $prevItem = $(this.videos[prevIndex]);
                this.playVideo($prevItem.data('video-id'), prevIndex);
            }
        },

        scrollToActive: function() {
            const $list = $('.jurible-video-list');
            const $active = $('.jurible-video-item.active');

            if ($active.length && $list.length) {
                const listTop = $list.offset().top;
                const listHeight = $list.height();
                const itemTop = $active.offset().top;
                const itemHeight = $active.outerHeight();

                if (itemTop < listTop || itemTop + itemHeight > listTop + listHeight) {
                    const scrollTo = $active.position().top + $list.scrollTop() - (listHeight / 2) + (itemHeight / 2);
                    $list.animate({ scrollTop: scrollTo }, 300);
                }
            }
        },

        loadProgress: function() {
            if (!juriblePlaylist.isLoggedIn || !this.collectionId) return;

            const self = this;

            $.ajax({
                url: juriblePlaylist.ajaxUrl + 'progress/' + this.collectionId,
                method: 'GET',
                headers: {
                    'X-WP-Nonce': juriblePlaylist.nonce
                },
                success: function(response) {
                    if (response.success && response.progress) {
                        self.progressData = response.progress;
                        self.updateProgressUI();
                    }
                }
            });
        },

        markStarted: function(videoId) {
            if (!juriblePlaylist.isLoggedIn) return;

            // Don't re-mark if already tracked
            if (this.progressData[videoId]) return;

            this.saveProgress(videoId, 'started', 0);
        },

        markCompleted: function(videoId) {
            if (!juriblePlaylist.isLoggedIn) return;

            // Don't re-mark if already completed
            if (this.progressData[videoId] && this.progressData[videoId].completed) return;

            const watchTime = Math.floor((Date.now() - this.videoStartTime) / 1000);
            this.saveProgress(videoId, 'completed', watchTime);

            // Update UI immediately
            this.progressData[videoId] = { status: 'completed', completed: true };
            this.updateVideoItemUI(videoId, true);
            this.updateProgressBar();
        },

        updateWatchTime: function() {
            if (!this.currentVideoId || !juriblePlaylist.isLoggedIn) return;

            const watchTime = Math.floor((Date.now() - this.videoStartTime) / 1000);
            const currentProgress = this.progressData[this.currentVideoId];

            // Only update if not completed
            if (currentProgress && currentProgress.completed) return;

            this.saveProgress(this.currentVideoId, 'started', watchTime);
        },

        saveProgress: function(videoId, status, watchTime) {
            $.ajax({
                url: juriblePlaylist.ajaxUrl + 'progress',
                method: 'POST',
                headers: {
                    'X-WP-Nonce': juriblePlaylist.nonce
                },
                data: {
                    video_id: videoId,
                    collection_id: this.collectionId,
                    status: status,
                    watch_time: watchTime
                },
                success: function(response) {
                    // Progress saved silently
                }
            });
        },

        updateProgressUI: function() {
            const self = this;

            $.each(this.progressData, function(videoId, data) {
                if (data.completed || data.status === 'completed') {
                    self.updateVideoItemUI(videoId, true);
                }
            });

            this.updateProgressBar();
        },

        updateVideoItemUI: function(videoId, completed) {
            const $item = $(`.jurible-video-item[data-video-id="${videoId}"]`);

            if (completed && !$item.hasClass('completed')) {
                $item.addClass('completed');

                // Add checkmark if not already present
                if (!$item.find('.jurible-video-completed').length) {
                    $item.append(`
                        <div class="jurible-video-completed">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    `);
                }
            }
        },

        updateProgressBar: function() {
            const totalVideos = this.videos.length;
            let completedCount = 0;

            $.each(this.progressData, function(videoId, data) {
                if (data.completed || data.status === 'completed') {
                    completedCount++;
                }
            });

            const percentage = totalVideos > 0 ? (completedCount / totalVideos) * 100 : 0;

            $('.jurible-progress-fill').css('width', percentage + '%');
            $('.jurible-completed-count').text(completedCount);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        JuriblePlaylist.init();
    });

})(jQuery);
