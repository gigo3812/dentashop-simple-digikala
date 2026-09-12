<?php
/**
 * Test Rewrite - WordPress loaded version
 */

// وردپرس رو لود کن
require_once __DIR__ . '/wp-load.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rewrite Test</title>
</head>
<body style="font-family:monospace;direction:ltr;text-align:left;padding:20px;background:#f5f5f5;">
    <h1>Rewrite & Permalink Test</h1>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>1. Apache & mod_rewrite</h2>
        <pre><?php
        if (function_exists('apache_get_modules')) {
            $mods = apache_get_modules();
            echo "mod_rewrite: " . (in_array('mod_rewrite', $mods) ? '✅ YES' : '❌ NO') . "\n";
        } else {
            echo "apache_get_modules: not available\n";
        }
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>2. Permalink Structure</h2>
        <pre><?php
        $structure = get_option('permalink_structure');
        echo "permalink_structure: " . ($structure ?: '(empty - default)') . "\n";
        echo "home_url: " . home_url('/') . "\n";
        echo "site_url: " . site_url('/') . "\n";
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>3. .htaccess</h2>
        <pre><?php
        $htaccess = ABSPATH . '.htaccess';
        echo ".htaccess path: " . $htaccess . "\n";
        echo "exists: " . (file_exists($htaccess) ? '✅ YES' : '❌ NO') . "\n";
        echo "writable: " . (is_writable($htaccess) ? '✅ YES' : '❌ NO') . "\n\n";
        if (file_exists($htaccess)) {
            echo "content:\n";
            echo htmlspecialchars(file_get_contents($htaccess));
        }
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>4. WooCommerce Permalinks</h2>
        <pre><?php
        if (class_exists('WooCommerce')) {
            $wc_permalinks = get_option('woocommerce_permalinks');
            print_r($wc_permalinks);
        } else {
            echo "WooCommerce not loaded\n";
        }
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>5. Product Categories - URL Test</h2>
        <pre><?php
        $terms = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
            'number'     => 10,
        ]);
        
        if (is_wp_error($terms)) {
            echo "Error: " . $terms->get_error_message() . "\n";
        } elseif (empty($terms)) {
            echo "No product categories found\n";
        } else {
            foreach ($terms as $term) {
                $link = get_term_link($term);
                echo "Category: {$term->name}\n";
                echo "  slug: {$term->slug}\n";
                echo "  link: " . (is_wp_error($link) ? '❌ ERROR: ' . $link->get_error_message() : $link) . "\n";
                echo "  is pretty: " . (strpos($link, '?') === false ? '✅ YES' : '❌ NO (has query string)') . "\n\n";
            }
        }
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-bottom:15px;">
        <h2>6. Shop Page</h2>
        <pre><?php
        $shop_id = wc_get_page_id('shop');
        echo "Shop page ID: {$shop_id}\n";
        echo "Shop URL: " . ($shop_id > 0 ? get_permalink($shop_id) : '—') . "\n";
        echo "Shop page title: " . ($shop_id > 0 ? get_the_title($shop_id) : '—') . "\n";
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;">
        <h2>7. Rewrite Rules (Sample)</h2>
        <pre><?php
        global $wp_rewrite;
        if ($wp_rewrite) {
            $rules = get_option('rewrite_rules');
            if (!$rules) {
                echo "❌ rewrite_rules is EMPTY - این مشکل اصلیه!\n";
                echo "باید بری به: Settings → Permalinks → Save Changes\n";
            } else {
                echo "Total rules: " . count($rules) . "\n\n";
                echo "Sample product_cat rules:\n";
                $count = 0;
                foreach ($rules as $pattern => $query) {
                    if (strpos($pattern, 'product_cat') !== false || strpos($query, 'product_cat') !== false) {
                        echo "  " . $pattern . "\n";
                        echo "    → " . $query . "\n";
                        $count++;
                        if ($count >= 5) break;
                    }
                }
                if ($count === 0) {
                    echo "  ❌ هیچ قانونی برای product_cat نیست!\n";
                }
            }
        }
        ?></pre>
    </div>
    
    <div style="background:#fff;padding:15px;border-radius:8px;margin-top:15px;">
        <h2>8. Test Pretty URL</h2>
        <p>یک دسته رو انتخاب کن و URL pretty رو ببین:</p>
        <?php if (!empty($terms) && !is_wp_error($terms)) : ?>
            <?php $first = $terms[0]; ?>
            <pre>Test URL: <?php echo esc_html(home_url('/product-category/' . $first->slug . '/')); ?></pre>
            <p>
                <a href="<?php echo esc_url(home_url('/product-category/' . $first->slug . '/')); ?>" 
                   target="_blank"
                   style="display:inline-block;padding:10px 20px;background:#2271b1;color:#fff;text-decoration:none;border-radius:4px;">
                    تست URL Pretty →
                </a>
            </p>
            <p>
                <a href="<?php echo esc_url(home_url('/?product_cat=' . $first->slug)); ?>" 
                   target="_blank"
                   style="display:inline-block;padding:10px 20px;background:#666;color:#fff;text-decoration:none;border-radius:4px;margin-right:10px;">
                    تست URL Query →
                </a>
            </p>
        <?php endif; ?>
    </div>
    
    <p style="margin-top:30px;padding:10px;background:#ffeb3b;border-radius:4px;">
        ⚠️ بعد از بررسی، این فایل رو حذف کن!
    </p>
</body>
</html>