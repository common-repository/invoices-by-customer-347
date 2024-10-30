<?php
$ano_00 = "--";
$ano_0  = date( "Y" );
$ano_1  = date( "Y", strtotime( '-1 year' ) );
$ano_2  = date( "Y", strtotime( '-2 year' ) );
$ano_3  = date( "Y", strtotime( '-3 year' ) );
$ano_4  = date( "Y", strtotime( '-4 year' ) );
$ano_5  = date( "Y", strtotime( '-5 year' ) );
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Invoices by Customer', '347-invoices' );?></title>

</head>

<body>
    <div class="wrap-mci">
        <form action="" method="post">
            <?php wp_nonce_field( 'datos_347', 'datos_347' );?>
            <div class="importe">
                <label><?php esc_html_e( 'Annual / Customer billing amount:', '347-invoices' );?></label><br>
                <input type="number" name="cifra" min="0" max="1000000"
                    value="<?php if ( isset( $cifra ) ) {echo esc_attr( $cifra );} else {echo esc_attr( 3005 );}?>">
            </div>
            <div class="ano">
                <label><?php esc_html_e( 'Year:', '347-invoices' );?> </label><br>
                <select name="ano">
                    <option value="<?php echo esc_html( $ano_00 ); ?>"><?php echo esc_html( $ano_00 ); ?></option>
                    <option value="<?php echo esc_html( $ano_0 ); ?>"><?php echo esc_html( $ano_0 ); ?></option>
                    <option value="<?php echo esc_html( $ano_1 ); ?>"><?php echo esc_html( $ano_1 ); ?></option>
                    <option value="<?php echo esc_html( $ano_2 ); ?>"><?php echo esc_html( $ano_2 ); ?></option>
                    <option value="<?php echo esc_html( $ano_3 ); ?>"><?php echo esc_html( $ano_3 ); ?></option>
                    <option value="<?php echo esc_html( $ano_4 ); ?>"><?php echo esc_html( $ano_4 ); ?></option>
                    <option value="<?php echo esc_html( $ano_5 ); ?>"><?php echo esc_html( $ano_5 ); ?></option>
                </select>
            </div>
            <div class="btn">
                <input type="submit" name="submit" value="<?php esc_html_e( 'Get billing', '347-invoices' );?>">
            </div>

        </form>
    </div>
</body>

</html>