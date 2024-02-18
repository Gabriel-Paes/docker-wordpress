<?php
/*
Plugin Name: Meu Plugin de Configuração
Description: Um simples plugin para editar e salvar configurações.
Version: 1.0
Author: Seu Nome
*/

// Adiciona a página de configurações ao menu do admin
add_action('admin_menu', 'meu_plugin_configuracao_menu');

function meu_plugin_configuracao_menu() {
    // Adiciona uma nova página ao menu de administração
    add_menu_page('Configurações do Meu Plugin', 'Meu Plugin', 'manage_options', 'meu-plugin-configuracao', 'meu_plugin_configuracao_page', 'dashicons-cloud');
}

// Mostra a página de configurações do plugin
function meu_plugin_configuracao_page() {
    ?>
    <div class="wrap">
        <h2>Configurações do Meu Plugin</h2>
        <form method="post" action="options.php">
            <?php
            // Registra as opções para o grupo de configurações
            settings_fields('meu-plugin-configuracao-group');
            do_settings_sections('meu-plugin-configuracao');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Minha Configuração</th>
                    <td><input type="text" name="minha_config" value="<?php echo esc_attr(get_option('minha_config')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registra as configurações
add_action('admin_init', 'meu_plugin_registrar_configuracao');

function meu_plugin_registrar_configuracao() {
    // Registra uma configuração para armazenamento no banco de dados
    register_setting('meu-plugin-configuracao-group', 'minha_config');
}