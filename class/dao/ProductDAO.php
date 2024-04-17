<?php

declare(strict_types=1);

namespace artifaille\publicstock\dao;

use artifaille\publicstock\model\Product;

/**
 * Reads products in database and outputs PHP objects
 */
class ProductDAO extends DAO
{
    /**
     * Fetch products that are available to sell (tosell = 1, stock > 0)
     *
     * @param  bool $includeOutOfStock Include products that are currently out of stock
     * @param  bool $includeOutOfStock Include products that don't have an image
     * @return Product[] Products found
     */
    public function readProducts(bool $includeOutOfStock, bool $includeWithoutImage): array
    {
        $products = [];
        $stockFilter = $includeOutOfStock ? '' : 'HAVING stock > 0';

		// If products without image are not to be displayed, an INNER JOIN on the images will exclude them
		$imageJoinType = $includeWithoutImage ? 'LEFT' : 'INNER';
        $query = <<<SQL
        	SELECT p.rowid, p.ref AS productRef, p.description, p.label, p.price_ttc, p.price,
			SUM(ps.reel) AS stock, n.label AS nature,
            file.share, cp.fk_categorie AS categoryId
            FROM {$this->tablePrefix}product AS p
            LEFT JOIN {$this->tablePrefix}product_stock AS ps
			ON ps.fk_product = p.rowid
		    LEFT JOIN {$this->tablePrefix}c_product_nature AS n
		    ON n.code = p.finished
		    AND n.active = 1
			LEFT JOIN {$this->tablePrefix}categorie_product AS cp
			ON cp.fk_product = p.rowid
        	{$imageJoinType} JOIN {$this->tablePrefix}ecm_files AS file
			ON src_object_type = 'product'
			AND src_object_id = p.rowid
			AND (
				file.filename LIKE "%.gif"
				OR file.filename LIKE "%.jpg"
				OR file.filename LIKE "%.jpeg"
				OR file.filename LIKE "%.png"
				OR file.filename LIKE "%.bmp"
				OR file.filename LIKE "%.webp"
				OR file.filename LIKE "%.xpm"
				OR file.filename LIKE "%.xbm"
			)
		    AND file.share IS NOT NULL
			WHERE tosell = 1
			GROUP BY p.rowid
			{$stockFilter};
SQL;
        $result = $this->doliDB->query($query);
        $row = $this->doliDB->fetch_array($result);
        while (\is_array($row)) {
            $rowid = (int)$row['rowid'];
            $categoryId = (int)($row['categoryId'] ?? -1);
            $products[$categoryId][$rowid] = new Product(
                $row['productRef'],
                $row['label'],
                $row['description'] ?? '',
                (float)($row['price'] ?? 0.0),
                (float)($row['price_ttc'] ?? 0.0),
                (int)($row['stock'] ?? 0),
                $row['share'] ?? '',
				(int)($row['categoryId'] ?? Product::UNCATEGORIZED),
				$row['nature'] ?? ''
            );
            $row = $this->doliDB->fetch_array($result);
        }
        return $products;
    }
}
