<?php

require __DIR__ . '/MySQLLexer.php';
require __DIR__ . '/MySQLParser.php';

$queries = [
    'simple' => 'SELECT 1',
    'complexSelect' => <<<ACID
WITH mytable AS (select 1 as a, `b`.c from dual)
SELECT HIGH_PRIORITY DISTINCT
	CONCAT("a", "b"),
	UPPER(z),
    DATE_FORMAT(col_a, '%Y-%m-%d %H:%i:%s') as formatted_date,
    DATE_ADD(col_b, INTERVAL 1 DAY) as date_plus_one,
	col_a
FROM 
(SELECT `mycol`, 997482686 FROM "mytable") as subquery
LEFT JOIN (SELECT a_column_yo from mytable) as t2 
    ON (t2.id = mytable.id AND t2.id = 1)
WHERE 1 = 3
GROUP BY col_a, col_b
HAVING 1 = 2
UNION SELECT * from table_cde
ORDER BY col_a DESC, col_b ASC
FOR UPDATE
ACID,
    'createTable' => <<<ACID
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) CHECK (price > 0),
    stock_quantity INT CHECK (stock_quantity >= 0),
    category ENUM('Electronics', 'Clothing', 'Books', 'Home', 'Beauty'),
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    `status` SET('Pending', 'Shipped', 'Delivered', 'Cancelled'),
    quantity INT CHECK (quantity > 0),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_date DATETIME,
    CONSTRAINT fk_customer
        FOREIGN KEY (customer_id) REFERENCES customers (customer_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_product
        FOREIGN KEY (product_id) REFERENCES products (product_id)
        ON DELETE CASCADE,
    INDEX idx_col_h_i (`col_h`, `col_i`),
    INDEX idx_col_g (`col_g`),
    UNIQUE INDEX idx_col_k (`col_k`),
    FULLTEXT INDEX idx_col_l (`col_l`)
) DEFAULT CHARACTER SET cp1250 COLLATE cp1250_general_ci;
ACID
];

printAST(parse($queries['complexSelect']));
printAST(parse($queries['createTable']));
// benchmarkParser($queries['acidTest']);

die();

function benchmarkParser($query) {
    $start = microtime(true);

    for ($i = 0; $i < 500; $i++) {
        parse($query);
    }

    $end = microtime(true);
    $executionTime = ($end - $start);

    echo "Execution time: " . $executionTime . " seconds";
}

function parse($query) {
    $lexer = new MySQLLexer($query);
    $parser = new MySQLParser($lexer);
    return $parser->query();
}

function printAST(ASTNode $ast, $indent = 0) {
    echo str_repeat(' ', $indent) . $ast . PHP_EOL;
    foreach($ast->children as $child) {
        printAST($child, $indent + 2);
    }
}

function printParserTree($parser) {
    $parser->query();
    $parser->printTree();
}

function printLexerTokens($lexer) {
    while($lexer->getNextToken()) {
        echo $lexer->getToken() . PHP_EOL;
        // var_dump($lexer->getToken()->getType());
        if($lexer->getToken()->getType() === MySQLLexer::EOF) {
            break;
        }
    }
}
