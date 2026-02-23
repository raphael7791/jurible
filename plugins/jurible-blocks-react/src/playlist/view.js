/**
 * Jurible Playlist Block - Frontend View
 * Loads videos from Bunny Stream and creates the playlist player
 */

(function() {
    'use strict';

    const PlaylistBlock = {
        containers: [],

        init: function() {
            this.initAllPlaylists();
            this.observeNewPlaylists();
        },

        initAllPlaylists: function() {
            const containers = document.querySelectorAll('.jurible-playlist-container:not([data-initialized])');
            containers.forEach(container => this.initPlaylist(container));
        },

        observeNewPlaylists: function() {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const containers = node.querySelectorAll ?
                                node.querySelectorAll('.jurible-playlist-container:not([data-initialized])') : [];
                            containers.forEach(container => this.initPlaylist(container));

                            if (node.classList && node.classList.contains('jurible-playlist-container') && !node.dataset.initialized) {
                                this.initPlaylist(node);
                            }
                        }
                    });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        },

        initPlaylist: function(container) {
            const collectionId = container.dataset.collectionId;
            const collectionName = container.dataset.collectionName;

            if (!collectionId) {
                container.innerHTML = '<div class="playlist-error">Aucune collection sélectionnée</div>';
                return;
            }

            container.dataset.initialized = 'true';
            container.innerHTML = '<div class="playlist-loading">Chargement de la playlist...</div>';

            // Fetch videos from the collection
            this.fetchVideos(collectionId)
                .then(videos => {
                    if (videos.length === 0) {
                        container.innerHTML = '<div class="playlist-error">Aucune vidéo dans cette collection</div>';
                        return;
                    }
                    this.renderPlaylist(container, collectionId, collectionName, videos);
                })
                .catch(error => {
                    console.error('Error loading playlist:', error);
                    container.innerHTML = '<div class="playlist-error">Erreur lors du chargement de la playlist</div>';
                });
        },

        fetchVideos: function(collectionId) {
            const apiUrl = '/wp-json/jurible/v1/bunny/collections/' + collectionId + '/videos';

            return fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.videos) {
                        return data.videos;
                    }
                    throw new Error('Invalid response');
                });
        },

        renderPlaylist: function(container, collectionId, collectionName, videos) {
            const firstVideo = videos[0];
            const pullZoneUrl = window.juriblePlaylistConfig?.pullZoneUrl || 'https://iframe.mediadelivery.net/embed/35843/';
            const nonce = window.wpApiSettings?.nonce || '';
            const isLoggedIn = !!nonce;

            container.innerHTML = `
                <div class="jurible-playlist" data-collection="${collectionId}">
                    <div class="jurible-player-container">
                        <div class="jurible-player-wrapper">
                            <iframe
                                id="jurible-player-${collectionId}"
                                src="${pullZoneUrl}${firstVideo.id}?autoplay=false&preload=true"
                                loading="lazy"
                                allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                                allowfullscreen="true"
                            ></iframe>
                        </div>
                        <div class="jurible-player-info">
                            <h3 class="jurible-current-title">${firstVideo.title}</h3>
                        </div>
                    </div>

                    <div class="jurible-playlist-controls">
                        <div class="jurible-playlist-header">
                            <span class="jurible-video-count">${videos.length} vidéos</span>
                            <span class="jurible-progress-count">
                                <span class="jurible-completed-count">0</span>/${videos.length} terminées
                            </span>
                        </div>
                        <label class="jurible-autoplay-toggle">
                            <input type="checkbox" id="jurible-autoplay-${collectionId}" checked>
                            <span class="jurible-toggle-slider"></span>
                            <span class="jurible-toggle-label">Lecture auto</span>
                        </label>
                    </div>

                    <div class="jurible-collection-progress">
                        <div class="jurible-progress-bar">
                            <div class="jurible-progress-fill" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="jurible-video-list">
                        ${videos.map((video, index) => `
                            <div class="jurible-video-item${index === 0 ? ' active' : ''}"
                                 data-video-id="${video.id}"
                                 data-index="${index}">
                                <span class="jurible-video-number">${index + 1}</span>
                                <div class="jurible-video-thumbnail">
                                    <img src="${video.thumbnail}" alt="${video.title}" loading="lazy">
                                    ${index === 0 ? `
                                        <div class="jurible-now-playing">
                                            <span></span><span></span><span></span>
                                        </div>
                                    ` : ''}
                                </div>
                                <div class="jurible-video-info">
                                    <span class="jurible-video-title">${video.title}</span>
                                    <span class="jurible-video-duration">${video.durationFormatted}</span>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            // Initialize player controls
            this.initPlayerControls(container, collectionId, videos, pullZoneUrl, isLoggedIn, nonce);
        },

        initPlayerControls: function(container, collectionId, videos, pullZoneUrl, isLoggedIn, nonce) {
            const playlist = container.querySelector('.jurible-playlist');
            const playerFrame = container.querySelector(`#jurible-player-${collectionId}`);
            const videoItems = container.querySelectorAll('.jurible-video-item');
            const autoplayToggle = container.querySelector(`#jurible-autoplay-${collectionId}`);
            const titleEl = container.querySelector('.jurible-current-title');
            const progressFill = container.querySelector('.jurible-progress-fill');
            const completedCountEl = container.querySelector('.jurible-completed-count');

            let currentIndex = 0;
            let currentVideoId = videos[0].id;
            let autoplay = true;
            let progressData = {};
            let videoStartTime = Date.now();

            // Load user progress if logged in
            if (isLoggedIn) {
                fetch('/wp-json/jurible-playlist/v1/progress/' + collectionId, {
                    headers: { 'X-WP-Nonce': nonce }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.progress) {
                        progressData = data.progress;
                        updateProgressUI();
                    }
                })
                .catch(err => console.log('Progress load error:', err));
            }

            // Video item click
            videoItems.forEach(item => {
                item.addEventListener('click', function() {
                    const videoId = this.dataset.videoId;
                    const index = parseInt(this.dataset.index, 10);
                    playVideo(videoId, index);
                });
            });

            // Autoplay toggle
            if (autoplayToggle) {
                autoplayToggle.addEventListener('change', function() {
                    autoplay = this.checked;
                });
            }

            // Listen for iframe messages
            window.addEventListener('message', function(event) {
                if (!event.origin.includes('mediadelivery.net') && !event.origin.includes('bunnycdn')) {
                    return;
                }

                try {
                    let data = event.data;
                    if (typeof data === 'string') {
                        data = JSON.parse(data);
                    }

                    // Video ended
                    if (data.event === 'videoEnded' || data.type === 'ended') {
                        markCompleted(currentVideoId);
                        if (autoplay && currentIndex < videos.length - 1) {
                            setTimeout(() => playVideo(videos[currentIndex + 1].id, currentIndex + 1), 1000);
                        }
                    }

                    // Video progress (90%+)
                    if (data.event === 'videoProgress' || data.type === 'progress') {
                        const percent = data.percent || data.progress || 0;
                        if (percent >= 0.9) {
                            markCompleted(currentVideoId);
                        }
                    }

                    // Video play
                    if (data.event === 'videoPlay' || data.type === 'play') {
                        markStarted(currentVideoId);
                    }
                } catch (e) {
                    // Not a JSON message
                }
            });

            function playVideo(videoId, index) {
                // Update UI
                videoItems.forEach(item => {
                    item.classList.remove('active');
                    const nowPlaying = item.querySelector('.jurible-now-playing');
                    if (nowPlaying) nowPlaying.remove();
                });

                const activeItem = container.querySelector(`.jurible-video-item[data-video-id="${videoId}"]`);
                if (activeItem) {
                    activeItem.classList.add('active');
                    const thumb = activeItem.querySelector('.jurible-video-thumbnail');
                    if (thumb) {
                        thumb.insertAdjacentHTML('beforeend', '<div class="jurible-now-playing"><span></span><span></span><span></span></div>');
                    }
                }

                // Update title
                titleEl.textContent = videos[index].title;

                // Update iframe
                playerFrame.src = pullZoneUrl + videoId + '?autoplay=true&preload=true';

                // Update state
                currentVideoId = videoId;
                currentIndex = index;
                videoStartTime = Date.now();

                markStarted(videoId);
                scrollToActive();
            }

            function scrollToActive() {
                const list = container.querySelector('.jurible-video-list');
                const active = container.querySelector('.jurible-video-item.active');
                if (list && active) {
                    const listRect = list.getBoundingClientRect();
                    const activeRect = active.getBoundingClientRect();
                    if (activeRect.top < listRect.top || activeRect.bottom > listRect.bottom) {
                        active.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }

            function markStarted(videoId) {
                if (!isLoggedIn || progressData[videoId]) return;
                saveProgress(videoId, 'started', 0);
            }

            function markCompleted(videoId) {
                if (!isLoggedIn) return;
                if (progressData[videoId] && progressData[videoId].completed) return;

                const watchTime = Math.floor((Date.now() - videoStartTime) / 1000);
                saveProgress(videoId, 'completed', watchTime);

                progressData[videoId] = { status: 'completed', completed: true };
                updateVideoItemUI(videoId, true);
                updateProgressBar();
            }

            function saveProgress(videoId, status, watchTime) {
                fetch('/wp-json/jurible-playlist/v1/progress', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    body: JSON.stringify({
                        video_id: videoId,
                        collection_id: collectionId,
                        status: status,
                        watch_time: watchTime
                    })
                }).catch(err => console.log('Progress save error:', err));
            }

            function updateProgressUI() {
                Object.keys(progressData).forEach(videoId => {
                    const data = progressData[videoId];
                    if (data.completed || data.status === 'completed') {
                        updateVideoItemUI(videoId, true);
                    }
                });
                updateProgressBar();
            }

            function updateVideoItemUI(videoId, completed) {
                const item = container.querySelector(`.jurible-video-item[data-video-id="${videoId}"]`);
                if (!item) return;

                if (completed && !item.classList.contains('completed')) {
                    item.classList.add('completed');
                    if (!item.querySelector('.jurible-video-completed')) {
                        item.insertAdjacentHTML('beforeend', `
                            <div class="jurible-video-completed">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        `);
                    }
                }
            }

            function updateProgressBar() {
                let completedCount = 0;
                Object.values(progressData).forEach(data => {
                    if (data.completed || data.status === 'completed') {
                        completedCount++;
                    }
                });

                const percentage = videos.length > 0 ? (completedCount / videos.length) * 100 : 0;
                progressFill.style.width = percentage + '%';
                completedCountEl.textContent = completedCount;
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => PlaylistBlock.init());
    } else {
        PlaylistBlock.init();
    }
})();
