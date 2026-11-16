
<?php
/**
 * GP_DentaShop
 * Custom WooCommerce Thank You Page
 */

defined( 'ABSPATH' ) || exit;

$order = isset( $order ) && $order instanceof WC_Order
    ? $order
    : false;

?>

<style>
/* =========================================
   GP_DentaShop - Thank You
========================================= */

.gpds-thankyou {
    direction: rtl;
    text-align: right;
    max-width: 1000px;
    margin: 35px auto;
    padding: 0 15px;
    color: #252525;
    font-size: 14px;
    line-height: 1.9;
}

.gpds-thankyou *,
.gpds-thankyou *::before,
.gpds-thankyou *::after {
    box-sizing: border-box;
}

.gpds-thankyou-card {
    background: #fff;
    border: 1px solid #eaeaea;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 3px 15px rgba(0,0,0,.035);
}

.gpds-thankyou-success {
    text-align: center;
    padding: 35px 20px;
    background: #f8fcf9;
    border-color: #e0f1e5;
}

.gpds-thankyou-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: #e6f7ed;
    color: #16a34a;
    font-size: 32px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}

.gpds-thankyou h1 {
    font-size: 23px;
    font-weight: 700;
    line-height: 1.8;
    margin: 0 0 12px;
    color: #202020;
}

.gpds-thankyou h2 {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 20px;
    color: #202020;
}

.gpds-thankyou-success p {
    color: #666;
    margin: 0 0 15px;
}

.gpds-thankyou-order-number {
    display: inline-block;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 10px;
    padding: 8px 20px;
    margin-top: 10px;
}

.gpds-thankyou-order-number strong {
    color: #16803c;
    font-size: 16px;
}

/* Order information */

.gpds-thankyou-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 13px 0;
    border-bottom: 1px solid #f0f0f0;
}

.gpds-thankyou-info-item:last-child {
    border-bottom: none;
}

.gpds-thankyou-info-item span {
    color: #777;
}

.gpds-thankyou-info-item strong {
    font-weight: 600;
    text-align: left;
}

.gpds-thankyou-total strong {
    color: #16803c;
    font-size: 18px;
}

/* Products */

.gpds-thankyou-product {
    display: grid;
    grid-template-columns: 75px minmax(0, 1fr) auto;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.gpds-thankyou-product:last-child {
    border-bottom: none;
}

.gpds-thankyou-product-image-wrap {
    width: 75px;
    height: 75px;
    border: 1px solid #eee;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}

.gpds-thankyou-product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.gpds-thankyou-product-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.gpds-thankyou-product-name {
    color: #252525;
    font-weight: 600;
    text-decoration: none;
}

.gpds-thankyou-product-name:hover {
    color: #16803c;
}

.gpds-thankyou-product-qty {
    font-size: 13px;
    color: #777;
}

.gpds-thankyou-product-price {
    font-weight: 600;
    white-space: nowrap;
}

/* Buttons */

.gpds-thankyou-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.gpds-thankyou-btn {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 12px 25px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none !important;
    transition: .2s ease;
    cursor: pointer;
}

.gpds-thankyou-btn-primary {
    background: #16803c;
    color: #fff !important;
}

.gpds-thankyou-btn-primary:hover {
    background: #11652f;
    color: #fff !important;
}

.gpds-thankyou-btn-secondary {
    background: #f5f5f5;
    color: #333 !important;
}

.gpds-thankyou-btn-secondary:hover {
    background: #eaeaea;
    color: #222 !important;
}

/* Failed payment */

.gpds-thankyou-failed {
    text-align: center;
}

.gpds-thankyou-failed .gpds-thankyou-icon {
    background: #fff1f1;
    color: #dc2626;
}

/* Mobile */

@media (max-width: 600px) {

    .gpds-thankyou {
        margin: 20px auto;
        padding: 0 12px;
        font-size: 13px;
    }

    .gpds-thankyou-card {
        padding: 18px;
        border-radius: 12px;
        margin-bottom: 15px;
    }

    .gpds-thankyou-success {
        padding: 25px 15px;
    }

    .gpds-thankyou h1 {
        font-size: 19px;
    }

    .gpds-thankyou h2 {
        font-size: 16px;
    }

    .gpds-thankyou-info-item {
        font-size: 13px;
        gap: 10px;
    }

    .gpds-thankyou-product {
        grid-template-columns: 60px minmax(0, 1fr);
        gap: 12px;
    }

    .gpds-thankyou-product-image-wrap {
        width: 60px;
        height: 60px;
    }

    .gpds-thankyou-product-price {
        grid-column: 2;
        font-size: 13px;
    }

    .gpds-thankyou-actions {
        flex-direction: column;
    }

    .gpds-thankyou-btn {
        width: 100%;
    }

}
</style>

<div class="gpds-thankyou">

<?php if ( ! $order ) : ?>

    <div class="gpds-thankyou-card gpds-thankyou-failed">

        <h2>اطلاعات سفارش در دسترس نیست</h2>

        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
           class="gpds-thankyou-btn gpds-thankyou-btn-primary">
            بازگشت به فروشگاه
        </a>

    </div>

<?php else : ?>

    <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

    <?php if ( $order->has_status( 'failed' ) ) : ?>

        <div class="gpds-thankyou-card gpds-thankyou-failed">

            <div class="gpds-thankyou-icon">!</div>

            <h1>پرداخت سفارش ناموفق بود</h1>

            <p>لطفاً برای پرداخت مجدد سفارش اقدام کنید.</p>

            <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"
               class="gpds-thankyou-btn gpds-thankyou-btn-primary">
                پرداخت مجدد
            </a>

        </div>

    <?php else : ?>

        <!-- Success -->

        <div class="gpds-thankyou-card gpds-thankyou-success">

            <div class="gpds-thankyou-icon">✓</div>

            <h1>سفارش شما با موفقیت ثبت شد!</h1>

            <p>
                از خرید شما از دنتاشاپ سپاسگزاریم.
                سفارش شما ثبت شده و در انتظار پردازش است.
            </p>

            <div class="gpds-thankyou-order-number">
                شماره سفارش:
                <strong>
                    #<?php echo esc_html( $order->get_order_number() ); ?>
                </strong>
            </div>

        </div>

        <!-- Order Summary -->

        <div class="gpds-thankyou-card">

            <h2>خلاصه سفارش</h2>

            <div class="gpds-thankyou-info-item">
                <span>تاریخ ثبت</span>
                <strong>
                    <?php
                    echo esc_html(
                        wc_format_datetime(
                            $order->get_date_created(),
                            'j F Y'
                        )
                    );
                    ?>
                </strong>
            </div>

            <div class="gpds-thankyou-info-item">
                <span>وضعیت سفارش</span>
                <strong>
                    <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
                </strong>
            </div>

            <div class="gpds-thankyou-info-item">
                <span>روش پرداخت</span>
                <strong>
                    <?php echo esc_html( $order->get_payment_method_title() ); ?>
                </strong>
            </div>

            <div class="gpds-thankyou-info-item gpds-thankyou-total">
                <span>مبلغ نهایی</span>
                <strong>
                    <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
                </strong>
            </div>

        </div>

        <!-- Products -->

        <div class="gpds-thankyou-card">

            <h2>محصولات سفارش</h2>

            <div class="gpds-thankyou-products">

                <?php foreach ( $order->get_items() as $item_id => $item ) :

                    $product = $item->get_product();

                    if ( ! $product ) {
                        continue;
                    }

                    $image = $product->get_image(
                        'woocommerce_thumbnail',
                        array(
                            'class' => 'gpds-thankyou-product-image',
                            'alt'   => $item->get_name(),
                        )
                    );

                ?>

                    <div class="gpds-thankyou-product">

                        <div class="gpds-thankyou-product-image-wrap">
                            <?php echo wp_kses_post( $image ); ?>
                        </div>

                        <div class="gpds-thankyou-product-info">

                            <a href="<?php echo esc_url( $product->get_permalink() ); ?>"
                               class="gpds-thankyou-product-name">

                                <?php echo esc_html( $item->get_name() ); ?>

                            </a>

                            <span class="gpds-thankyou-product-qty">
                                تعداد:
                                <?php echo esc_html( $item->get_quantity() ); ?>
                            </span>

                        </div>

                        <div class="gpds-thankyou-product-price">

                            <?php
                            echo wp_kses_post(
                                $order->get_formatted_line_subtotal( $item )
                            );
                            ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <!-- Actions -->

        <div class="gpds-thankyou-actions">

            <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>"
               class="gpds-thankyou-btn gpds-thankyou-btn-primary">
                مشاهده سفارش
            </a>

            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
               class="gpds-thankyou-btn gpds-thankyou-btn-secondary">
                ادامه خرید
            </a>

        </div>

    <?php endif; ?>

    <?php
    do_action(
        'woocommerce_thankyou_' . $order->get_payment_method(),
        $order->get_id()
    );

    do_action( 'woocommerce_thankyou', $order->get_id() );
    ?>

<?php endif; ?>

</div>
