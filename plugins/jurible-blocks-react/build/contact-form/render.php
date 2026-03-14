<?php
/**
 * Contact Form Block - Server-side rendering
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

$description    = $attributes['description'] ?? '';
$buttonText     = $attributes['buttonText'] ?? 'Envoyer mon message →';
$successMessage = $attributes['successMessage'] ?? 'Votre message a bien été envoyé.';
$recipientEmail = $attributes['recipientEmail'] ?? '';
$subjects       = $attributes['subjects'] ?? 'Question générale,Support technique,Partenariat,Autre';
$subjectList    = array_map('trim', explode(',', $subjects));

$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'jurible-contact-form'
]);

$nonce = wp_create_nonce('jurible_contact_form');
?>

<div <?php echo $wrapper_attributes; ?>
     data-nonce="<?php echo esc_attr($nonce); ?>"
     data-success-message="<?php echo esc_attr($successMessage); ?>"
     data-recipient="<?php echo esc_attr($recipientEmail); ?>">

    <?php if ($description) : ?>
        <p class="jurible-contact-form__description"><?php echo esc_html($description); ?></p>
    <?php endif; ?>

    <form class="jurible-contact-form__form" novalidate>
        <div class="jurible-contact-form__row">
            <div class="jurible-contact-form__field">
                <label class="jurible-contact-form__label">Prénom <span class="jurible-contact-form__required">*</span></label>
                <input
                    type="text"
                    name="firstName"
                    class="jurible-contact-form__input"
                    placeholder="Jean"
                    required
                />
            </div>

            <div class="jurible-contact-form__field">
                <label class="jurible-contact-form__label">Nom <span class="jurible-contact-form__required">*</span></label>
                <input
                    type="text"
                    name="lastName"
                    class="jurible-contact-form__input"
                    placeholder="Dupont"
                    required
                />
            </div>
        </div>

        <div class="jurible-contact-form__field">
            <label class="jurible-contact-form__label">Email <span class="jurible-contact-form__required">*</span></label>
            <input
                type="email"
                name="email"
                class="jurible-contact-form__input"
                placeholder="jean.dupont@email.com"
                required
            />
        </div>

        <div class="jurible-contact-form__field">
            <label class="jurible-contact-form__label">Sujet</label>
            <select name="subject" class="jurible-contact-form__select">
                <option value="">Choisir un sujet...</option>
                <?php foreach ($subjectList as $subject) : ?>
                    <option value="<?php echo esc_attr($subject); ?>"><?php echo esc_html($subject); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="jurible-contact-form__field">
            <label class="jurible-contact-form__label">Message <span class="jurible-contact-form__required">*</span></label>
            <textarea
                name="message"
                class="jurible-contact-form__textarea"
                placeholder="Décrivez votre demande..."
                rows="5"
                required
            ></textarea>
        </div>

        <!-- Honeypot -->
        <div class="jurible-contact-form__hp" aria-hidden="true" tabindex="-1">
            <input type="text" name="website" autocomplete="off" tabindex="-1" />
        </div>

        <button type="submit" class="jurible-contact-form__btn">
            <?php echo esc_html($buttonText); ?>
        </button>
    </form>

    <div class="jurible-contact-form__status" role="alert" aria-live="polite"></div>
</div>
