<?php
    $customCss = ($psTheme === '') ? [] : ['publicstock/css/' . $psTheme . '.css'];
	\top_htmlhead('', '', 0, 0, ['publicstock/js/publicstock.js'], $customCss, 1, 1, 1);
	if ($psCss !== '') {
	?>
		<style rel="stylesheet" type="text/css"><?= $psCss ?></style>
	<?php
	}
?>
<body>
    <article class="ps_main">
        <section>
        <?php
        if (empty($psProducts)) {
            echo $langs->trans('NoProductToDisplay');
        } else {
            $uncategorizedLabel = $langs->trans('Uncategorized');

            // Enable categories tabs only if there is more than one category
            if (\count($psProducts) > 1) {
                $withCategories = true;
                $cssCategories = 'ps_withCategories';
            } else {
                $withCategories = false;
                $cssCategories = 'ps_withoutCategories';
            }
            ?>
                <div id="ps_categories" class="<?= $cssCategories ?>">
                <?php
                if ($withCategories) {
                    ?>
                    <ul class="ps_tabs">
                    <?php
                    foreach ($psProducts as $categoryId => $products) {
						// Skip uncategorized products if option to display them is not set
						if (!isset($psCategories[$categoryId]) && $psShowUncategorized === false) {
							continue;
						}
                        $categoryLabel = $psCategories[$categoryId] ?? $uncategorizedLabel;
                        ?>
                        <li class="ps_tab_title">
                            <a href="#tab_<?= $categoryId ?>"><?= $categoryLabel ?></a>
                        </li>
                    <?php } ?>
                    </ul>
                <?php } ?>
            <?php
            foreach ($psProducts as $categoryId => $products) {
				// Skip uncategorized products if option to display them is not set
			    if (!isset($psCategories[$categoryId]) && $psShowUncategorized === false) {
					continue;
				}
                $categoryLabel = $psCategories[$categoryId] ?? $uncategorizedLabel;
                ?>
                <div class="ps_category" id="tab_<?= $categoryId ?>">
                <?php
                foreach ($products as $product) {
                    if ($psShowImage && $product->getImageShare() !== '') {
                        $imageURL = (\defined('DOL_MAIN_URL_ROOT') ? (DOL_MAIN_URL_ROOT . '/') : '')
                        	. 'document.php?hashp=' . $product->getImageShare();
                        $imageBlock = <<<HTML
                    <div class="ps_product_image_block">
                        <img class="ps_product_image" alt="Product image" src="{$imageURL}">
			    	</div>
HTML;
                    } else {
                        $imageBlock = '';
                    }
                    ?>
                    <div class="ps_product">
                        <h3 class="ps_product_label"><?= $product->getLabel() ?></h3>
						<p class="ps_product_ref"><?= '(' . $product->getReference() . ')' ?></p>
						<?php if ($psShowDescription || $psShowImage) { ?>
							<div class="ps_product_image_desc">
                        		<?= $imageBlock ?>
							<?php if ($psShowDescription) { ?>
                        		<div class="ps_product_desc">
    								<?= $product->getDescription() ?>
                        		</div>
							<?php } ?>
							</div>
						<?php } ?>
						<ul class="ps_product_other">
						<?php if ($psPriceType === 'included' || $psPriceType === 'both') { ?>
                            <li class="ps_product_price">
								<label><?= $langs->trans('Price') . ' (' . $langs->trans('TTC') . ')' ?></label
								><span><?= $product->getPriceTTC() . $psCurrencySymbol ?></span>
							</li>
						<?php } ?>
						<?php if ($psPriceType === 'excluded' || $psPriceType === 'both') { ?>
                            <li class="ps_product_price">
								<label><?= $langs->trans('Price') . ' (' . $langs->trans('HT') . ')' ?></label
								><span><?= \round($product->getPrice(), 2) ?></span>
							</li>
						<?php } ?>
						<?php if ($psShowStock) { ?>
                            <li class="ps_product_stock">
								<label><?= $langs->trans('Stock') ?></label
								><span><?= \round($product->getStock(), 2) ?></span>
							</li>
						<?php } ?>
						<?php if ($psShowNature) { ?>
                            <li class="ps_product_finished">
								<label><?= $langs->trans('NatureOfProductShort') ?></label
								><span><?= $langs->trans($product->getNature()) ?></span>
							</li>
						<?php } ?>
						</ul>
                    </div>
                    <?php
                }
                ?>
                </div>
                <?php
            }
            ?>
            </div>
        <?php } ?>
        </section>
    </article>
</body>
