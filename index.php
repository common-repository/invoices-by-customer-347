<?php
/**
 * Plugin Name: Invoices by Customer 347
 * Plugin URI: https://mci-desarrollo.es/plugins
 * Author: MCI Desarrollo
 * Version: 1.0.3
 * Author URI: https://mci-desarrollo.es
 * Text Domain: invoices-by-customer-347
 * Description: Obtain quarterly summary, annual summary and list of invoices per client that exceed a user-selectable amount. It is necessary to have installed the WooCommerce PDF Invoices & Packing Slips plugin.
 */

// Link to the style sheet      //Enlaza con la hoja de estilos
function mci347_enqueue_options_style()
{
    wp_enqueue_style( 'style-347', plugins_url( '/style-347.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'mci347_enqueue_options_style' );

load_plugin_textdomain(
    'invoices-by-customer-347',
    false,
    basename( dirname( __FILE__ ) ) . '/languages'
);
//=================================================================

// Plugin activation in Wordpress
// Activación plugin en Wordpress
register_activation_hook( __FILE__, 'mci_347_init' );

function mci_347_init()
{

    global $wpdb;
    $tabla_mci347    = sanitize_text_field( $wpdb->prefix ) . 'mci347';
    $charset_collate = $wpdb->get_charset_collate(); // get the order of the current DB to respect the same order
    //obtiene el orden de la BD actual para respetar el mismo orden

    // Create new table
    //Crear nueva tabla
    $query = "CREATE TABLE IF NOT EXISTS $tabla_mci347 (
		id int(20) NOT NULL AUTO_INCREMENT,
		post_id int(20) NOT NULL,
		fecha date NOT NULL,
		nif varchar(12),
		factura varchar(15) NOT NULL,
		importe float,
		empresa varchar(80),
		nombre_apellidos varchar(80),
		cliente int(6) NOT NULL,
		trimestre int(1) NOT NULL,
		UNIQUE (id)
	) $charset_collate";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php'; // Load the file where the dB Delta function is in case it is not loaded // Carga el archivo donde está la función dB Delta por si no está cargada
    dbDelta( $query ); // Execute the query // Ejecuta la query
}

// Deactivation Plugin in Wordpress // Desactivación Plugin en Wordpress
register_deactivation_hook( __FILE__, 'mci_347_desactivate' );
// Delete table on deactivation // Eliminar tabla al desactivar
function mci_347_desactivate()
{
    global $wpdb;
    $tabla_mci347 = sanitize_text_field( $wpdb->prefix ) . 'mci347';
    $wpdb->query( "DROP TABLE " . sanitize_text_field( $tabla_mci347 ) );
}

//=================================================================
// Add a new item to the desktop administration menu
// Create submenu and plugin configuration page

// Agrega nuevo item al menu de administracion del escritorio
// Crea submenú y página de configuración del plugin
add_action( "admin_menu", "mci_347_menu" );

function mci_347_menu()
{
    add_submenu_page( 'woocommerce',
        'Invoices by Customer 347', // Page title
        'Invoices by Customer', // Menu title
        'manage_options', // permissions
        'invoices-by-customer-347', // slug
        'mci_347_lista_clientes', // function that contains the configuration code
        75// position
    );
}
//=================================================================
// Plugin configuration function
// Función configuración del plugin

function mci_347_lista_clientes()
{

    echo '<div class="wrap"><h1>';
    esc_html_e( 'Invoices by Customer 347', 'invoices-by-customer-347' );
    echo '</h1></div>';

    if ( isset( $_POST['submit'] ) ) { //If the Submit button has been pressed //Si se ha pulsado el botón Submit

        // Clean table mci347 //Limpiar tabla mci347
        global $wpdb;
        $tabla_mci347 = sanitize_text_field( $wpdb->prefix ) . 'mci347';
        $wpdb->query( "TRUNCATE TABLE " . sanitize_text_field( $tabla_mci347 ) );

        // Collect form data // Recoger datos formulario
        if ( !empty( $_POST['cifra'] ) && is_numeric( $_POST['cifra'] ) ) {
            $cifra = sanitize_text_field( $_POST['cifra'] ); // collect the number //recoge la cifra
        }
        if (  ( !empty( $_POST['ano'] ) ) && ( $_POST['ano'] != '--' && is_numeric( $_POST['ano'] ) ) ) {
            $ano = sanitize_text_field( $_POST['ano'] ); // collect the year //recoge el año
        } else {
            echo '<br><p class="error">';
            esc_html_e( 'You must select a year and numeric value amount', 'invoices-by-customer-347' );
            echo '</p>';
        }

        if (  ( !empty( $_POST['ano'] ) ) && ( $_POST['ano'] != '--' ) ) { //Continue if the user has selected a year //Continúa si el usuario ha seleccionado un año

// Collect results Invoices from the DB target table/year   // Recoger resultados Facturas de la BD tabla postmeta /año
            // Query table postmeta-> post_id    // Consulta tabla postmeta->post_id

            $tabla_postmeta = sanitize_text_field( $wpdb->prefix ) . 'postmeta';
            $post_ids       = $wpdb->get_results( "SELECT post_id FROM $tabla_postmeta
		WHERE (meta_key = '_wcpdf_invoice_date_formatted') AND YEAR(meta_value)=" . $ano );

// Collect ids of credit notes from the table wcpdf_credit_note_number if there is a Premium Version of PDF_IPS.     //Recoger ids de Abonos de la tabla wcpdf_credit_note_number si hay Versión Premium de PDF_IPS.
            // The order_id of wcpdf_credit_note_number matches the post_id of the postmeta table     // El order_id de wcpdf_credit_note_number coincide con el post_id de la tabla postmeta
            if ( class_exists( 'WooCommerce_PDF_IPS_Pro' ) ) {
                $tabla_creditnote = sanitize_text_field( $wpdb->prefix ) . 'wcpdf_credit_note_number';
                $abonos_ids       = $wpdb->get_results( "SELECT order_id AS post_id2 FROM $tabla_creditnote
		WHERE YEAR(date)=" . $ano );
            }

            foreach ( $post_ids as $valor ) {
                $datos = $valor->post_id;
                $datos = (int) $datos;

                // Extract data from the postmeta table //Extraer datos de la tabla postmeta
                $post_id = sanitize_text_field( $datos );

                $fecha = get_post_meta( sanitize_text_field( $datos ), '_wcpdf_invoice_date_formatted', true ); //función de wordpress para extraer datos de tabla postmeta

                $nif = get_post_meta( sanitize_text_field( $datos ), 'NIF', true );

                $factura = get_post_meta( sanitize_text_field( $datos ), '_wcpdf_invoice_number', true );

                $importe = get_post_meta( sanitize_text_field( $datos ), '_order_total', true );

                $empresa = get_post_meta( sanitize_text_field( $datos ), '_billing_company', true );

                $nombre = get_post_meta( sanitize_text_field( $datos ), '_billing_first_name', true );

                $apellidos = get_post_meta( sanitize_text_field( $datos ), '_billing_last_name', true );

                $nombre_apellidos = $nombre . ' ' . $apellidos;

                $cliente = get_post_meta( sanitize_text_field( $datos ), '_customer_user', true );

                $trimestre = get_post_meta( sanitize_text_field( $datos ), '_wcpdf_invoice_date_formatted', true ); //función de wordpress para extraer datos de tabla postmeta

                $trimestre = date( "m", strtotime( $trimestre ) );
                if ( $trimestre == 1 || $trimestre == 2 || $trimestre == 3 ) {
                    $result_trimestre = 1;
                } elseif ( $trimestre == 4 || $trimestre == 5 || $trimestre == 6 ) {
                    $result_trimestre = 2;
                } elseif ( $trimestre == 7 || $trimestre == 8 || $trimestre == 9 ) {
                    $result_trimestre = 3;
                } elseif ( $trimestre == 10 || $trimestre == 11 || $trimestre == 12 ) {
                    $result_trimestre = 4;
                }

                // Create data array // Crear array datos
                $datos_postmeta = array(
                    'post_id'          => $post_id,
                    'fecha'            => $fecha,
                    'nif'              => $nif,
                    'factura'          => $factura,
                    'importe'          => $importe,
                    'empresa'          => $empresa,
                    'nombre_apellidos' => $nombre_apellidos,
                    'cliente'          => $cliente,
                    'trimestre'        => $result_trimestre,

                );
                // Write data to table mci347  // Grabar datos en tabla mci347
                $wpdb->insert( $tabla_mci347, $datos_postmeta );
            }

//=====================================================================
            // SUMMARY TABLE BY CLIENT (YEAR AND QUARTERS)  // TABLA RESUMEN POR CLIENTE (AÑO Y TRIMESTRES)
            // Print table header  //Imprime cabecera de la tabla
            echo '<div class="wrap"><h2>';
            esc_html_e( 'Total sales by Customer in year ', 'invoices-by-customer-347' );
            echo esc_html( $ano );
            echo ' ';
            esc_html_e( '(Clients that exceed the ', 'invoices-by-customer-347' );
            echo esc_html( $cifra );
            echo get_woocommerce_currency_symbol();
            echo ')';
            echo '</h2></div>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>';
            esc_html_e( 'COMPANY', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'NAME', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'VAT', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( '1 QUARTER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( '2 QUARTER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( '3 QUARTER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( '4 QUARTER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'YEAR TOTAL', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'CUSTOMER NUMBER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '</tr></thead>';

            // Get a list of clients that exceed the figure and the sums by quarter and year
            // Read mci347 table data, add it and print it on screen

            //Obtener listado de clientes que superan la cifra y las sumas por trimestre y año
            //Leer datos de tabla mci347, sumarlos e imprimirlos en pantalla

            $sumas_merge = $wpdb->get_results(
                "SELECT cliente AS refcliente, empresa, nombre_apellidos, nif,
		(SELECT SUM(importe) FROM $tabla_mci347 WHERE trimestre=1 AND cliente=refcliente GROUP BY cliente AND trimestre) AS primero,
		(SELECT SUM(importe) FROM $tabla_mci347 WHERE trimestre=2 AND cliente=refcliente GROUP BY cliente AND trimestre) AS segundo,
		(SELECT SUM(importe) FROM $tabla_mci347 WHERE trimestre=3 AND cliente=refcliente GROUP BY cliente AND trimestre) AS tercero,
		(SELECT SUM(importe) FROM $tabla_mci347 WHERE trimestre=4 AND cliente=refcliente GROUP BY cliente AND trimestre) AS cuarto,
		(SELECT SUM(importe) FROM $tabla_mci347 WHERE cliente=refcliente GROUP BY cliente AND trimestre HAVING SUM(importe) >=$cifra) AS totalano
		FROM $tabla_mci347 GROUP BY cliente HAVING SUM(importe) >=$cifra ORDER BY nombre_apellidos"
            );

            // Print all data on the screen
            //Imprime los datos en pantalla
            foreach ( $sumas_merge as $clave_sumas => $valor_sumas ) {

                echo '<td>' . esc_html( $valor_sumas->empresa ) . '</td>';
                echo '<td>' . esc_html( $valor_sumas->nombre_apellidos ) . '</td>';
                echo '<td>' . esc_html( $valor_sumas->nif ) . '</td>';

                $primero = sanitize_text_field( $valor_sumas->primero );
                $primero = (float) $primero;
                $primero = number_format( $primero, 2, ',', '.' );
                if ( isset( $primero ) ) {echo '<td>' . esc_html( $primero ) . '</td>';} else {echo '<td>0</td>';}

                $segundo = sanitize_text_field( $valor_sumas->segundo );
                $segundo = (float) $segundo;
                $segundo = number_format( $segundo, 2, ',', '.' );
                if ( isset( $segundo ) ) {echo '<td>' . esc_html( $segundo ) . '</td>';} else {echo '<td>0</td>';}

                $tercero = sanitize_text_field( $valor_sumas->tercero );
                $tercero = (float) $tercero;
                $tercero = number_format( $tercero, 2, ',', '.' );
                if ( isset( $tercero ) ) {echo '<td>' . esc_html( $tercero ) . '</td>';} else {echo '<td>0</td>';}

                $cuarto = sanitize_text_field( $valor_sumas->cuarto );
                $cuarto = (float) $cuarto;
                $cuarto = number_format( $cuarto, 2, ',', '.' );
                if ( isset( $cuarto ) ) {echo '<td>' . esc_html( $cuarto ) . '</td>';} else {echo '<td>0</td>';}

                $totalano = sanitize_text_field( $valor_sumas->totalano );
                $totalano = (float) $totalano;
                $totalano = number_format( $totalano, 2, ',', '.' );
                if ( isset( $totalano ) ) {echo '<td>' . esc_html( $totalano ) . '</td>';} else {echo '<td>0</td>';}
                echo '<td>' . esc_html( $valor_sumas->refcliente ) . '</td>';

                echo '</tr>';
            }
            echo '</table>';

//=====================================================================
            // INVOICE LISTING TABLE
            // TABLA LISTADO DE FACTURAS

            // Print table header
            //Imprime cabecera de la tabla
            echo '<div class="wrap"><br><h2>';
            esc_html_e( 'Invoice listing (Customers who exceed the ', 'invoices-by-customer-347' );
            echo esc_html( $cifra );
            echo get_woocommerce_currency_symbol();
            echo ' in year ' . esc_html( $ano ) . ')';

            echo '</h2></div>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';

            echo '<th>';
            esc_html_e( 'DATE', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'VAT', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'INVOICE NUMBER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'AMOUNT', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'COMPANY', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'NAME', 'invoices-by-customer-347' );
            echo '</th>';
            echo '<th>';
            esc_html_e( 'CUSTOMER NUMBER', 'invoices-by-customer-347' );
            echo '</th>';
            echo '</tr></thead>';

            // Read table data mci347 and print it on screen
            // Get a list of customer invoices that exceed the figure

            //Leer datos de tabla mci347 e imprimirlos en pantalla
            //Obtener listado de facturas de los clientes que superan la cifra
            $lista_facturas_mci347 = $wpdb->get_results( "SELECT fecha, nif, factura, importe, empresa, nombre_apellidos, cliente
		FROM $tabla_mci347
		WHERE cliente IN(SELECT cliente FROM $tabla_mci347 GROUP BY cliente HAVING SUM(importe) >=" . $cifra . ") ORDER BY nombre_apellidos" );

            //Print data on the screen
            //Imprime los datos en pantalla
            foreach ( $lista_facturas_mci347 as $clave2 => $valor2 ) {
                echo '<tr>';
                $fecha         = sanitize_text_field( $valor2->fecha );
                $fecha_spanish = date( "d-m-Y", strtotime( $fecha ) );
                echo '<td>' . esc_html( $fecha_spanish ) . '</td>';
                echo '<td>' . esc_html( $valor2->nif ) . '</td>';
                echo '<td>' . esc_html( $valor2->factura ) . '</td>';
                echo '<td>' . esc_html( $valor2->importe ) . '</td>';
                echo '<td>' . esc_html( $valor2->empresa ) . '</td>';
                echo '<td>' . esc_html( $valor2->nombre_apellidos ) . '</td>';
                echo '<td>' . esc_html( $valor2->cliente ) . '</td>';
            }
            echo '</tr></table>';

        } //End isset submit

    } //End    Continue if the user has selected a year //Continúa si el usuario ha seleccionado un año

//Print the form //Imprime el Formulario
    require 'form-347.php';

} // End function lista_clientes
