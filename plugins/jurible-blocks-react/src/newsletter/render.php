<?php
/**
 * Newsletter Block - Server-side rendering
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

$title       = $attributes['title'] ?? 'Newsletter';
$description = $attributes['description'] ?? '';
$placeholder = $attributes['placeholder'] ?? 'Votre email';
$buttonText  = $attributes['buttonText'] ?? "S'inscrire";
$variant     = $attributes['variant'] ?? 'dark';
$layout      = $attributes['layout'] ?? 'horizontal';

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => "jurible-newsletter jurible-newsletter--{$variant} jurible-newsletter--{$layout}"
]);

$unique_id = 'jrbl-nl-' . wp_unique_id();
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ($title) : ?>
        <h4 class="jurible-newsletter__title"><?php echo esc_html($title); ?></h4>
    <?php endif; ?>

    <?php if ($description) : ?>
        <p class="jurible-newsletter__description"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <form class="jurible-newsletter__form" id="<?php echo esc_attr($unique_id); ?>" action="#" method="post">
        <input
            type="email"
            name="email"
            class="jurible-newsletter__input"
            placeholder="<?php echo esc_attr($placeholder); ?>"
            required
        />
        <button type="submit" class="jurible-newsletter__btn">
            <?php echo esc_html($buttonText); ?>
        </button>
    </form>
    <p class="jurible-newsletter__consent" style="margin-top:6px;font-size:11px;opacity:0.6;line-height:1.4;">En soumettant, vous acceptez de recevoir nos conseils par email. Désinscription en 1 clic à tout moment.</p>
    <p class="jurible-newsletter__msg" style="display:none;margin-top:8px;font-size:13px;"></p>
</div>

<script>
(function(){
  var form = document.getElementById("<?php echo esc_js($unique_id); ?>");
  if (!form) return;
  var msg = form.parentElement.querySelector(".jurible-newsletter__msg");
  form.addEventListener("submit", function(e) {
    e.preventDefault();
    var email = form.email.value.trim();
    if (!email) return;
    var btn = form.querySelector("button");
    btn.disabled = true;
    btn.textContent = "Envoi…";
    msg.style.display = "none";

    fetch("https://ecole.aideauxtd.com/wp-json/jurible/v1/subscribe", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email: email })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        msg.style.display = "block";
        msg.style.color = "#10B981";
        msg.textContent = "Inscription réussie ! Vérifiez votre boîte mail.";
        form.reset();
      } else {
        msg.style.display = "block";
        msg.style.color = "#EF4444";
        msg.textContent = "Erreur. Veuillez réessayer.";
      }
      btn.disabled = false;
      btn.textContent = "<?php echo esc_js($buttonText); ?>";
    })
    .catch(function() {
      msg.style.display = "block";
      msg.style.color = "#EF4444";
      msg.textContent = "Erreur de connexion.";
      btn.disabled = false;
      btn.textContent = "<?php echo esc_js($buttonText); ?>";
    });
  });
})();
</script>
