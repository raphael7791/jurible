<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Handle form submissions ───
$action_msg = '';

if ( isset( $_POST['jam_save_rule'] ) && check_admin_referer( 'jam_save_rule' ) ) {
    $data = [
        'rule_name'       => $_POST['rule_name'] ?? '',
        'sc_product_id'   => $_POST['sc_product_id'] ?? '',
        'fcom_course_ids' => $_POST['fcom_course_ids'] ?? [],
        'crm_tag_ids'     => $_POST['crm_tag_ids'] ?? [],
        'crm_list_ids'    => $_POST['crm_list_ids'] ?? [],
        'credit_amount'   => $_POST['credit_amount'] ?? 0,
    ];

    $edit_id = absint( $_POST['rule_id'] ?? 0 );

    if ( $edit_id ) {
        JAM_Access_Rules::update( $edit_id, $data );
        $action_msg = 'Règle mise à jour.';
    } else {
        JAM_Access_Rules::create( $data );
        $action_msg = 'Règle créée.';
    }
}

if ( isset( $_GET['delete_rule'] ) && check_admin_referer( 'jam_delete_rule_' . $_GET['delete_rule'] ) ) {
    JAM_Access_Rules::delete( absint( $_GET['delete_rule'] ) );
    $action_msg = 'Règle supprimée.';
}

// ─── Mode: list or edit ───
$editing   = false;
$edit_rule = null;

if ( isset( $_GET['edit_rule'] ) ) {
    $editing   = true;
    $edit_rule = JAM_Access_Rules::get( absint( $_GET['edit_rule'] ) );
}

if ( isset( $_GET['add_rule'] ) ) {
    $editing = true;
}

// ─── Data for form ───
$sc_products  = jam_dashboard_get_sc_products();
$fcom_courses = jam_dashboard_get_fcom_courses();

// FluentCRM data
$crm_tags  = [];
$crm_lists = [];
$crm_active = function_exists( 'FluentCrmApi' );

if ( $crm_active ) {
    try {
        $crm_tags  = \FluentCrm\App\Models\Tag::orderBy( 'title' )->get()->toArray();
        $crm_lists = \FluentCrm\App\Models\Lists::orderBy( 'title' )->get()->toArray();
    } catch ( \Exception $e ) {
        $crm_active = false;
    }
}

?>
<div class="jam-wrap">
    <h1>
        Règles d'accès
        <?php if ( ! $editing ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&add_rule=1' ) ); ?>" class="page-title-action">Ajouter une règle</a>
        <?php endif; ?>
    </h1>

    <?php if ( $action_msg ) : ?>
        <div class="jam-notice jam-notice--success"><?php echo esc_html( $action_msg ); ?></div>
    <?php endif; ?>

    <?php if ( $editing ) : ?>
        <!-- ─── Edit / Add Form ─── -->
        <?php
        $current = $edit_rule ? (object) [
            'id'              => $edit_rule->id,
            'rule_name'       => $edit_rule->rule_name,
            'sc_product_id'   => $edit_rule->sc_product_id,
            'fcom_course_ids' => JAM_Access_Rules::get_course_ids( $edit_rule ),
            'crm_tag_ids'     => JAM_Access_Rules::get_crm_tag_ids( $edit_rule ),
            'crm_list_ids'    => JAM_Access_Rules::get_crm_list_ids( $edit_rule ),
            'credit_amount'   => $edit_rule->credit_amount ?? 0,
        ] : (object) [
            'id'              => 0,
            'rule_name'       => '',
            'sc_product_id'   => '',
            'fcom_course_ids' => [],
            'crm_tag_ids'     => [],
            'crm_list_ids'    => [],
            'credit_amount'   => 0,
        ];
        ?>
        <div class="jam-section">
            <div class="jam-section__header">
                <h2><?php echo $current->id ? 'Modifier la règle' : 'Nouvelle règle'; ?></h2>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules' ) ); ?>" class="button">Retour à la liste</a>
            </div>
            <div class="jam-section__body jam-section__body--padded">
                <form method="post" class="jam-form">
                    <?php wp_nonce_field( 'jam_save_rule' ); ?>
                    <input type="hidden" name="rule_id" value="<?php echo esc_attr( $current->id ); ?>">

                    <div class="jam-form-row">
                        <label for="rule_name">Nom de la règle</label>
                        <input type="text" id="rule_name" name="rule_name" value="<?php echo esc_attr( $current->rule_name ); ?>" placeholder="Ex: Académie (nouveau)" required>
                        <p class="description">Nom interne pour identifier cette règle.</p>
                    </div>

                    <div class="jam-form-row">
                        <label for="sc_product_id">Produit SureCart</label>
                        <select id="sc_product_id" name="sc_product_id" required>
                            <option value="">— Sélectionner un produit —</option>
                            <?php foreach ( $sc_products as $product ) : ?>
                                <option value="<?php echo esc_attr( $product['id'] ); ?>" <?php selected( $current->sc_product_id, $product['id'] ); ?>>
                                    <?php echo esc_html( $product['name'] ); ?> (<?php echo esc_html( $product['type'] ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="jam-form-row">
                        <label>Cours Fluent Community</label>
                        <?php if ( empty( $fcom_courses ) ) : ?>
                            <p class="description">Aucun cours disponible.</p>
                        <?php else : ?>
                            <div class="jam-checkbox-actions">
                                <a href="#" id="jam-select-all-courses">Tout sélectionner</a>
                                <a href="#" id="jam-deselect-all-courses">Tout désélectionner</a>
                            </div>
                            <div class="jam-checkbox-grid">
                                <?php foreach ( $fcom_courses as $course ) : ?>
                                    <label>
                                        <input type="checkbox" name="fcom_course_ids[]" value="<?php echo esc_attr( $course['id'] ); ?>"
                                            <?php checked( in_array( (int) $course['id'], $current->fcom_course_ids, true ) ); ?>>
                                        <?php echo esc_html( $course['title'] ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="jam-form-row">
                        <label for="credit_amount">Crédits générateur à ajouter</label>
                        <input type="text" id="credit_amount" name="credit_amount" value="<?php echo esc_attr( $current->credit_amount ); ?>" style="max-width: 100px;">
                        <p class="description">0 = pas de crédits. Ex: 20 pour un "Pack 20 crédits".</p>
                    </div>

                    <?php if ( $crm_active ) : ?>
                        <div class="jam-form-row">
                            <label>Tags FluentCRM</label>
                            <div class="jam-checkbox-grid">
                                <?php foreach ( $crm_tags as $tag ) : ?>
                                    <label>
                                        <input type="checkbox" name="crm_tag_ids[]" value="<?php echo esc_attr( $tag['id'] ); ?>"
                                            <?php checked( in_array( (int) $tag['id'], $current->crm_tag_ids, true ) ); ?>>
                                        <?php echo esc_html( $tag['title'] ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="jam-form-row">
                            <label>Listes FluentCRM</label>
                            <div class="jam-checkbox-grid">
                                <?php foreach ( $crm_lists as $list ) : ?>
                                    <label>
                                        <input type="checkbox" name="crm_list_ids[]" value="<?php echo esc_attr( $list['id'] ); ?>"
                                            <?php checked( in_array( (int) $list['id'], $current->crm_list_ids, true ) ); ?>>
                                        <?php echo esc_html( $list['title'] ); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="jam-form-row">
                            <p class="description">FluentCRM n'est pas actif. Les tags et listes seront disponibles après installation.</p>
                        </div>
                    <?php endif; ?>

                    <p>
                        <button type="submit" name="jam_save_rule" class="button button-primary">
                            <?php echo $current->id ? 'Mettre à jour' : 'Créer la règle'; ?>
                        </button>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules' ) ); ?>" class="button">Annuler</a>
                    </p>
                </form>
            </div>
        </div>

    <?php else : ?>
        <!-- ─── Rules List ─── -->
        <?php $rules = JAM_Access_Rules::get_all(); ?>
        <div class="jam-section">
            <div class="jam-section__body">
                <?php if ( empty( $rules ) ) : ?>
                    <div class="jam-empty">
                        Aucune règle d'accès. <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&add_rule=1' ) ); ?>">Créer la première règle</a>.
                    </div>
                <?php else : ?>
                    <table class="jam-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Produit SureCart</th>
                                <th>Cours associés</th>
                                <th>Crédits</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $rules as $rule ) :
                                $course_ids   = JAM_Access_Rules::get_course_ids( $rule );
                                $product_name = '—';
                                foreach ( $sc_products as $p ) {
                                    if ( $p['id'] === $rule->sc_product_id ) {
                                        $product_name = $p['name'];
                                        break;
                                    }
                                }
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $rule->rule_name ); ?></strong></td>
                                    <td><?php echo esc_html( $product_name ); ?></td>
                                    <td>
                                        <span class="jam-badge jam-badge--blue"><?php echo count( $course_ids ); ?> cours</span>
                                    </td>
                                    <td>
                                        <?php if ( $rule->credit_amount > 0 ) : ?>
                                            <span class="jam-badge jam-badge--orange"><?php echo intval( $rule->credit_amount ); ?> crédits</span>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="jam-actions">
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&edit_rule=' . $rule->id ) ); ?>">Modifier</a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=jam-rules&delete_rule=' . $rule->id ), 'jam_delete_rule_' . $rule->id ) ); ?>"
                                           class="jam-delete"
                                           onclick="return confirm('Supprimer cette règle ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
