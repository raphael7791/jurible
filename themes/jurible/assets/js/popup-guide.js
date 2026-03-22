(function() {
  "use strict";

  var ENDPOINT = "https://ecole.aideauxtd.com/wp-json/jurible/v1/subscribe";
  var COOKIE   = "jrbl_popup_seen";
  var COOKIE_DAYS = 2;

  function hasCookie() {
    return document.cookie.indexOf(COOKIE + "=1") !== -1;
  }
  function setCookie() {
    var d = new Date();
    d.setTime(d.getTime() + COOKIE_DAYS * 86400000);
    document.cookie = COOKIE + "=1;expires=" + d.toUTCString() + ";path=/;SameSite=Lax";
  }

  var check = '<svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
  var cross = '<svg viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

  var html = '<div class="jrbl-popup-overlay" id="jrblPopup">'
    + '<div class="jrbl-popup">'
    + '<button class="jrbl-popup__close" id="jrblPopupClose">' + cross + '</button>'
    + '<div class="jrbl-popup__step is-active" id="jrblStep1">'
    + '<div class="jrbl-popup__top">'
    + '<div class="jrbl-popup__mockup"><div class="jrbl-popup__book"><div class="jrbl-popup__book-spine"></div><div class="jrbl-popup__book-cover"><div class="jrbl-popup__book-text">10 CONSEILS<br>POUR EXCELLER<br>EN DROIT</div></div></div></div>'
    + '<div class="jrbl-popup__question">Voulez-vous augmenter vos notes en droit\u00a0?</div>'
    + '<div class="jrbl-popup__sub">Recevez gratuitement notre guide \u2014 10 conseils que vos profs n\u2019enseignent pas.</div>'
    + '</div>'
    + '<div class="jrbl-popup__actions">'
    + '<button class="jrbl-popup__btn-oui" id="jrblBtnOui">' + check + ' Oui, je veux le guide gratuit</button>'
    + '<button class="jrbl-popup__btn-non" id="jrblBtnNon">Non merci, je ne veux pas am\u00e9liorer mes notes</button>'
    + '</div>'
    + '<div class="jrbl-popup__reassurance">'
    + '<div class="jrbl-popup__reassurance-item">' + check + ' 100% gratuit</div>'
    + '<div class="jrbl-popup__reassurance-item">' + check + ' Sans engagement</div>'
    + '<div class="jrbl-popup__reassurance-item">' + check + ' D\u00e9sinscription 1 clic</div>'
    + '</div>'
    + '</div>'
    + '<div class="jrbl-popup__step" id="jrblStep2">'
    + '<div class="jrbl-popup__top">'
    + '<div class="jrbl-popup__question">O\u00f9 vous envoyer le guide\u00a0?</div>'
    + '<div class="jrbl-popup__sub">Vous le recevrez imm\u00e9diatement dans votre bo\u00eete mail.</div>'
    + '</div>'
    + '<form class="jrbl-popup__form" id="jrblForm">'
    + '<input type="text" class="jrbl-popup__input" name="first_name" placeholder="Votre pr\u00e9nom" required>'
    + '<input type="email" class="jrbl-popup__input" name="email" placeholder="Votre adresse email *" required>'
    + '<button type="submit" class="jrbl-popup__btn-submit" id="jrblSubmit">Recevoir le guide gratuitement \u2192</button>'
    + '<p class="jrbl-popup__privacy">En soumettant, vous acceptez de recevoir nos conseils par email. D\u00e9sinscription en 1 clic \u00e0 tout moment.</p>'
    + '</form>'
    + '<div class="jrbl-popup__reassurance" style="padding-top:0;padding-bottom:20px;">'
    + '<div class="jrbl-popup__reassurance-item">' + check + ' 25 000+ \u00e9tudiants</div>'
    + '<div class="jrbl-popup__reassurance-item">' + check + ' Depuis 2018</div>'
    + '</div>'
    + '</div>'
    + '<div class="jrbl-popup__step" id="jrblStep3">'
    + '<div class="jrbl-popup__top">'
    + '<div class="jrbl-popup__question">Merci\u00a0!</div>'
    + '<div class="jrbl-popup__sub">V\u00e9rifiez votre bo\u00eete mail.</div>'
    + '</div>'
    + '<div class="jrbl-popup__success">'
    + '<div class="jrbl-popup__success-icon">\u2709\uFE0F</div>'
    + '<div class="jrbl-popup__success-title">Guide envoy\u00e9\u00a0!</div>'
    + '<div class="jrbl-popup__success-text">Consultez votre bo\u00eete de r\u00e9ception (et vos spams). Bonne lecture\u00a0!</div>'
    + '</div>'
    + '</div>'
    + '</div></div>';

  var wrapper = document.createElement("div");
  wrapper.innerHTML = html;
  document.body.appendChild(wrapper.firstChild);

  var overlay = document.getElementById("jrblPopup");
  var step1   = document.getElementById("jrblStep1");
  var step2   = document.getElementById("jrblStep2");
  var step3   = document.getElementById("jrblStep3");

  function showPopup() {
    if (hasCookie()) return;
    overlay.classList.add("is-visible");
    document.body.style.overflow = "hidden";
  }

  function hidePopup() {
    overlay.classList.remove("is-visible");
    document.body.style.overflow = "";
    setCookie();
  }

  function goStep(n) {
    step1.classList.remove("is-active");
    step2.classList.remove("is-active");
    step3.classList.remove("is-active");
    if (n === 1) step1.classList.add("is-active");
    if (n === 2) step2.classList.add("is-active");
    if (n === 3) step3.classList.add("is-active");
  }

  document.getElementById("jrblPopupClose").addEventListener("click", hidePopup);
  overlay.addEventListener("click", function(e) {
    if (e.target === overlay) hidePopup();
  });

  document.getElementById("jrblBtnOui").addEventListener("click", function() { goStep(2); });
  document.getElementById("jrblBtnNon").addEventListener("click", hidePopup);

  document.getElementById("jrblForm").addEventListener("submit", function(e) {
    e.preventDefault();
    var btn = document.getElementById("jrblSubmit");
    var email = this.email.value.trim();
    var firstName = this.first_name.value.trim();
    if (!email) return;

    btn.disabled = true;
    btn.textContent = "Envoi en cours\u2026";

    var body = { email: email };
    if (firstName) body.first_name = firstName;

    fetch(ENDPOINT, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        goStep(3);
        setCookie();
        setTimeout(hidePopup, 4000);
      } else {
        btn.disabled = false;
        btn.textContent = "Recevoir le guide gratuitement \u2192";
        alert("Une erreur est survenue. Veuillez r\u00e9essayer.");
      }
    })
    .catch(function() {
      btn.disabled = false;
      btn.textContent = "Recevoir le guide gratuitement \u2192";
      alert("Erreur de connexion. Veuillez r\u00e9essayer.");
    });
  });

  // Exit-intent trigger
  var triggered = false;
  document.addEventListener("mouseout", function(e) {
    if (triggered || hasCookie()) return;
    if (e.clientY < 5 && e.relatedTarget == null) {
      triggered = true;
      showPopup();
    }
  });

  // Open from CTA links with href="#popup-guide"
  document.addEventListener("click", function(e) {
    var link = e.target.closest('a[href="#popup-guide"]');
    if (link) {
      e.preventDefault();
      goStep(2);
      overlay.classList.add("is-visible");
      document.body.style.overflow = "hidden";
    }
  });

})();
