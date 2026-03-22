<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Handle form submissions ───
$action_msg = '';

if ( isset( $_POST['jam_save_rule'] ) && check_admin_referer( 'jam_save_rule' ) ) {
    // Build credit_price_map from submitted pairs
    $credit_price_map = [];
    if ( ! empty( $_POST['credit_price_ids'] ) && is_array( $_POST['credit_price_ids'] ) ) {
        $price_ids     = $_POST['credit_price_ids'];
        $price_amounts = $_POST['credit_price_amounts'] ?? [];
        foreach ( $price_ids as $i => $pid ) {
            $pid = sanitize_text_field( $pid );
            $amt = absint( $price_amounts[ $i ] ?? 0 );
            if ( $pid && $amt > 0 ) {
                $credit_price_map[ $pid ] = $amt;
            }
        }
    }

    $data = [
        'rule_name'           => $_POST['rule_name'] ?? '',
        'sc_product_id'       => $_POST['sc_product_id'] ?? '',
        'fcom_course_ids'     => $_POST['fcom_course_ids'] ?? [],
        'crm_tag_ids'         => $_POST['crm_tag_ids'] ?? [],
        'crm_list_ids'        => $_POST['crm_list_ids'] ?? [],
        'credit_amount'       => $_POST['credit_amount'] ?? 0,
        'credit_price_map'    => ! empty( $credit_price_map ) ? $credit_price_map : null,
        'aide_perso_enabled'  => ! empty( $_POST['aide_perso_enabled'] ) ? 1 : 0,
        'aide_perso_copies'   => $_POST['aide_perso_copies'] ?? 0,
        'aide_perso_questions'=> $_POST['aide_perso_questions'] ?? 0,
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

// Build list of product IDs that already have a rule (for indicator)
$all_rules_list     = JAM_Access_Rules::get_all();
$ruled_product_ids  = [];
foreach ( $all_rules_list as $r ) {
    $ruled_product_ids[ $r->sc_product_id ] = $r->rule_name;
}

// Split new/old then group each
$new_product_ids = get_option( 'jam_new_product_ids', [] );
$manual_old_ids  = get_option( 'jam_old_product_ids', [] );
$cutoff          = strtotime( '2026-02-01' );

// Filter out PDF products (handled natively by SureCart, not Fluent Community)
$sc_products_filtered = array_filter( $sc_products, function( $p ) {
    return ! preg_match( '/^(Fiches|Pack|Manuel)/i', $p['name'] );
} );

$new_products_list = [];
$old_products_list = [];
foreach ( $sc_products_filtered as $product ) {
    $created  = intval( $product['created_at'] ?? 0 );
    $auto_new = $created >= $cutoff;

    if ( isset( $manual_old_ids[ $product['id'] ] ) ) {
        $is_new = false;
    } elseif ( isset( $new_product_ids[ $product['id'] ] ) ) {
        $is_new = true;
    } else {
        $is_new = $auto_new;
    }

    if ( $is_new ) {
        $new_products_list[] = $product;
    } else {
        $old_products_list[] = $product;
    }
}

$grouped_new = JAM_Helpers::group_products( $new_products_list );
$grouped_old = JAM_Helpers::group_products( $old_products_list );

// Build course name lookup
$course_name_map = [];
foreach ( $fcom_courses as $c ) {
    $course_name_map[ $c['id'] ] = $c['title'];
}

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
            'id'                  => $edit_rule->id,
            'rule_name'           => $edit_rule->rule_name,
            'sc_product_id'       => $edit_rule->sc_product_id,
            'fcom_course_ids'     => JAM_Access_Rules::get_course_ids( $edit_rule ),
            'crm_tag_ids'         => JAM_Access_Rules::get_crm_tag_ids( $edit_rule ),
            'crm_list_ids'        => JAM_Access_Rules::get_crm_list_ids( $edit_rule ),
            'credit_amount'       => $edit_rule->credit_amount ?? 0,
            'credit_price_map'    => JAM_Access_Rules::get_credit_price_map( $edit_rule ),
            'aide_perso_enabled'  => $edit_rule->aide_perso_enabled ?? 0,
            'aide_perso_copies'   => $edit_rule->aide_perso_copies ?? 0,
            'aide_perso_questions'=> $edit_rule->aide_perso_questions ?? 0,
        ] : (object) [
            'id'                  => 0,
            'rule_name'           => '',
            'sc_product_id'       => '',
            'fcom_course_ids'     => [],
            'crm_tag_ids'         => [],
            'crm_list_ids'        => [],
            'credit_amount'       => 0,
            'credit_price_map'    => [],
            'aide_perso_enabled'  => 0,
            'aide_perso_copies'   => 0,
            'aide_perso_questions'=> 0,
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

                    <?php
                    // Determine if current product is new or old
                    $current_is_new = false;
                    foreach ( $new_products_list as $np ) {
                        if ( $np['id'] === $current->sc_product_id ) {
                            $current_is_new = true;
                            break;
                        }
                    }
                    ?>
                    <input type="hidden" name="sc_product_id" id="sc_product_id" value="<?php echo esc_attr( $current->sc_product_id ); ?>">

                    <div class="jam-form-row">
                        <label>Nouveaux produits</label>
                        <select id="jam-select-new" class="jam-product-select">
                            <option value="">— Nouveau produit —</option>
                            <?php foreach ( $grouped_new as $group_name => $group_items ) : ?>
                                <optgroup label="<?php echo esc_attr( $group_name ); ?>">
                                    <?php foreach ( $group_items as $product ) :
                                        $has_rule = isset( $ruled_product_ids[ $product['id'] ] ) && $product['id'] !== $current->sc_product_id;
                                        $prices_text = ! empty( $product['prices'] ) ? ' — ' . implode( ', ', $product['prices'] ) : '';
                                        $rule_marker = $has_rule ? ' [Règle: ' . $ruled_product_ids[ $product['id'] ] . ']' : '';
                                    ?>
                                        <option value="<?php echo esc_attr( $product['id'] ); ?>"
                                            <?php selected( $current_is_new && $current->sc_product_id === $product['id'] ); ?>
                                            <?php if ( $has_rule ) : ?>style="color:#999;"<?php endif; ?>>
                                            <?php echo esc_html( $product['name'] . $prices_text . $rule_marker ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="jam-form-row">
                        <label>Anciens produits</label>
                        <select id="jam-select-old" class="jam-product-select">
                            <option value="">— Ancien produit —</option>
                            <?php foreach ( $grouped_old as $group_name => $group_items ) : ?>
                                <optgroup label="<?php echo esc_attr( $group_name ); ?>">
                                    <?php foreach ( $group_items as $product ) :
                                        $has_rule = isset( $ruled_product_ids[ $product['id'] ] ) && $product['id'] !== $current->sc_product_id;
                                        $prices_text = ! empty( $product['prices'] ) ? ' — ' . implode( ', ', $product['prices'] ) : '';
                                        $rule_marker = $has_rule ? ' [Règle: ' . $ruled_product_ids[ $product['id'] ] . ']' : '';
                                    ?>
                                        <option value="<?php echo esc_attr( $product['id'] ); ?>"
                                            <?php selected( ! $current_is_new && $current->sc_product_id === $product['id'] ); ?>
                                            <?php if ( $has_rule ) : ?>style="color:#999;"<?php endif; ?>>
                                            <?php echo esc_html( $product['name'] . $prices_text . $rule_marker ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( ! empty( $ruled_product_ids ) ) : ?>
                            <p class="description">Les produits avec [Règle: ...] ont déjà une règle configurée.</p>
                        <?php endif; ?>
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
                        <label for="credit_amount">Crédits générateur (par défaut)</label>
                        <input type="text" id="credit_amount" name="credit_amount" value="<?php echo esc_attr( $current->credit_amount ); ?>" style="max-width: 100px;">
                        <p class="description">Crédits ajoutés au solde de l'utilisateur. 0 = pas de crédits. Utilisé sauf si un mapping par prix est défini ci-dessous.</p>
                    </div>

                    <div class="jam-form-row">
                        <label>Mapping prix &rarr; crédits <small>(optionnel)</small></label>
                        <p class="description" style="margin-bottom:8px;">Pour les produits avec plusieurs prix (ex: Minos 5€ = 10 crédits, 17€ = 100 crédits). Si renseigné, le price_id de l'achat détermine les crédits au lieu du champ par défaut ci-dessus.</p>
                        <div id="jam-price-map-rows">
                            <?php if ( ! empty( $current->credit_price_map ) ) : ?>
                                <?php foreach ( $current->credit_price_map as $pid => $amt ) : ?>
                                    <div class="jam-price-map-row" style="display:flex;gap:8px;margin-bottom:6px;align-items:center;">
                                        <input type="text" name="credit_price_ids[]" value="<?php echo esc_attr( $pid ); ?>" placeholder="Price ID SureCart" style="flex:1;">
                                        <input type="number" name="credit_price_amounts[]" value="<?php echo esc_attr( $amt ); ?>" placeholder="Crédits" style="width:100px;" min="0">
                                        <button type="button" class="button jam-remove-price-row" title="Supprimer">&times;</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="jam-add-price-row">+ Ajouter un prix</button>
                    </div>

                    <div class="jam-form-row">
                        <label>Aide personnalisée</label>
                        <label style="display:inline-flex;align-items:center;gap:6px;margin-bottom:8px;">
                            <input type="checkbox" name="aide_perso_enabled" id="jam-aide-perso-toggle" value="1" <?php checked( $current->aide_perso_enabled ); ?>>
                            Activer l'aide personnalisée (questions de cours + corrections de copies)
                        </label>
                        <div id="jam-aide-perso-fields" style="<?php echo $current->aide_perso_enabled ? '' : 'display:none;'; ?>margin-top:8px;">
                            <div style="display:flex;gap:16px;">
                                <div>
                                    <label for="aide_perso_questions" style="font-weight:normal;font-size:13px;">Questions par mois</label>
                                    <input type="number" id="aide_perso_questions" name="aide_perso_questions" value="<?php echo esc_attr( $current->aide_perso_questions ); ?>" min="0" style="width:100px;display:block;">
                                </div>
                                <div>
                                    <label for="aide_perso_copies" style="font-weight:normal;font-size:13px;">Copies par mois</label>
                                    <input type="number" id="aide_perso_copies" name="aide_perso_copies" value="<?php echo esc_attr( $current->aide_perso_copies ); ?>" min="0" style="width:100px;display:block;">
                                </div>
                            </div>
                            <p class="description" style="margin-top:6px;">Limites de crédits pour l'aide personnalisée. 0 = illimité.</p>
                        </div>
                        <script>
                        document.getElementById('jam-aide-perso-toggle').addEventListener('change', function() {
                            document.getElementById('jam-aide-perso-fields').style.display = this.checked ? '' : 'none';
                        });
                        </script>
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
        <?php
        $rules = JAM_Access_Rules::get_all();
        $new_rules = [];
        $old_rules = [];
        foreach ( $rules as $rule ) {
            if ( strpos( $rule->rule_name, '(' ) !== false ) {
                $old_rules[] = $rule;
            } else {
                $new_rules[] = $rule;
            }
        }
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'new';
        $display_rules = $active_tab === 'old' ? $old_rules : $new_rules;
        ?>
        <div class="jam-section">
            <div style="display:flex;gap:0;border-bottom:2px solid #E5E7EB;margin-bottom:0;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&tab=new' ) ); ?>"
                   style="padding:10px 20px;font-weight:600;font-size:14px;text-decoration:none;border-bottom:2px solid <?php echo $active_tab === 'new' ? '#2563EB' : 'transparent'; ?>;margin-bottom:-2px;color:<?php echo $active_tab === 'new' ? '#2563EB' : '#6B7280'; ?>;">
                    Nouveaux (<?php echo count( $new_rules ); ?>)
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&tab=old' ) ); ?>"
                   style="padding:10px 20px;font-weight:600;font-size:14px;text-decoration:none;border-bottom:2px solid <?php echo $active_tab === 'old' ? '#2563EB' : 'transparent'; ?>;margin-bottom:-2px;color:<?php echo $active_tab === 'old' ? '#2563EB' : '#6B7280'; ?>;">
                    Anciens (<?php echo count( $old_rules ); ?>)
                </a>
            </div>
            <div class="jam-section__body">
                <?php if ( empty( $display_rules ) ) : ?>
                    <div class="jam-empty">
                        Aucune règle dans cet onglet. <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&add_rule=1' ) ); ?>">Créer une règle</a>.
                    </div>
                <?php else : ?>
                    <table class="jam-table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Cours</th>
                                <th>Crédits</th>
                                <th>Aide perso</th>
                                <th>CRM</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $display_rules as $rule ) :
                                $course_ids   = JAM_Access_Rules::get_course_ids( $rule );
                                $course_names = [];
                                foreach ( $course_ids as $cid ) {
                                    $course_names[] = $course_name_map[ $cid ] ?? '#' . $cid;
                                }
                                $tag_ids  = JAM_Access_Rules::get_crm_tag_ids( $rule );
                                $list_ids = JAM_Access_Rules::get_crm_list_ids( $rule );
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $rule->rule_name ); ?></strong></td>
                                    <td>
                                        <?php if ( ! empty( $course_names ) ) : ?>
                                            <span class="jam-badge jam-badge--blue jam-rule-toggle" style="cursor:pointer;" data-rule-id="<?php echo esc_attr( $rule->id ); ?>">
                                                <span class="jam-rule-toggle__arrow">&#9654;</span>
                                                <?php echo count( $course_ids ); ?>
                                            </span>
                                            <div class="jam-rule-courses" id="jam-rule-courses-<?php echo esc_attr( $rule->id ); ?>" style="display:none;margin-top:6px;">
                                                <small style="color:#646970;"><?php echo esc_html( implode( ', ', $course_names ) ); ?></small>
                                            </div>
                                        <?php else : ?>
                                            <span class="jam-badge jam-badge--gray">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $price_map = JAM_Access_Rules::get_credit_price_map( $rule );
                                        if ( ! empty( $price_map ) ) :
                                            foreach ( $price_map as $pid => $amt ) :
                                                $short_pid = substr( $pid, 0, 8 ) . '…';
                                        ?>
                                                <span class="jam-badge jam-badge--orange" title="<?php echo esc_attr( $pid ); ?>"><?php echo intval( $amt ); ?>cr</span>
                                        <?php
                                            endforeach;
                                        elseif ( $rule->credit_amount > 0 ) : ?>
                                            <span class="jam-badge jam-badge--orange"><?php echo intval( $rule->credit_amount ); ?></span>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $rule->aide_perso_enabled ) ) : ?>
                                            <span class="jam-badge jam-badge--blue"><?php echo intval( $rule->aide_perso_questions ); ?>Q/<?php echo intval( $rule->aide_perso_copies ); ?>C</span>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $tag_ids ) || ! empty( $list_ids ) ) : ?>
                                            <span class="jam-badge jam-badge--gray" style="font-size:11px;"><?php
                                                $crm_parts = [];
                                                if ( ! empty( $tag_ids ) ) $crm_parts[] = count( $tag_ids ) . 'T';
                                                if ( ! empty( $list_ids ) ) $crm_parts[] = count( $list_ids ) . 'L';
                                                echo implode( ' ', $crm_parts );
                                            ?></span>
                                        <?php else : ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="jam-actions">
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=jam-rules&edit_rule=' . $rule->id ) ); ?>">Modifier</a>
                                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=jam-rules&delete_rule=' . $rule->id ), 'jam_delete_rule_' . $rule->id ) ); ?>"
                                           class="jam-delete"
                                           onclick="return confirm('Supprimer cette règle ?');">Suppr.</a>
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
