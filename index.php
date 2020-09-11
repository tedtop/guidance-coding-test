<?php
/**
 * Guidance Coding Challenge
 * Author: Ted Toporkov
 * Date: 3/10/2016
 */

define('SQLITE3_DB', 'books.db');

/**
 * Get book titles from SQLite db grouped by first letter of book title
 *
 * @return array
 * @throws Exception
 */
function get_book_titles()
{
    $db      = new SQLite3(SQLITE3_DB);
    $sql     = "SELECT SUBSTR(title, 1, 1) AS first_letter, * FROM books";
    $books   = array();
    $results = $db->query($sql);

    // Group results from books by first letter of title
    while ($row = $results->fetchArray($mode=SQLITE3_ASSOC)) {
        $books[$row['first_letter']][] = $row;
    }
    return $books;
}

/**
 * Get book details from SQLite db by ISBN
 *
 * @param $isbn
 * @return array
 * @throws Exception
 */
function get_book_details($isbn)
{
    $db  = new SQLite3(SQLITE3_DB);
    $sql = "SELECT * FROM books WHERE isbn='$isbn' ";

    if ($book = $db->querySingle($sql, true)) {
        return $book;
    } else throw new Exception('Unable to find book with ISBN: $isbn');
}

/**
 * Wrap output content with boilerplate html
 *
 * @param $content
 * @param $title
 * @return string
 */
function get_html_skel($content=null, $title="Reading List")
{
    return $html = <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>$title</title>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
}

/**
 * Output alphabet links header
 *
 * @return string
 */
function output_alpha_header(&$books)
{
    $output = '';
    $output .= "<div><h2>";
    foreach (range('A', 'Z') as $letter) {
        if (array_key_exists($letter, $books)) {
            $output .= "<a href=\"#$letter\">$letter<a/> ";
        } else {
            $output .= $letter . " ";
        }
    }
    $output .= "</h2><div>\n";
    return $output;
}

/**
 * Output alphabet links header
 *
 * @return string
 */
function output_book_titles(&$books)
{
    // Output book titles & links grouped by first letter of title
    $output = '';
    $output .= "<div>\n";
    foreach(range('A', 'Z') as $letter) {
        if (array_key_exists($letter, $books)) {

            $output .= "<h3 id=\"$letter\">$letter</h3>\n";

            $output .= "<ul>\n";
            foreach($books[$letter] as $book) {
                //echo "<li><a href=\"details.php?isbn={$book['isbn']}\">{$book['title']}</a></li>\n";

                $self = basename(__FILE__);
                $isbn = $book['isbn']; $title = $book['title'];
                $output .= "<li><a href=\"$self/isbn:$isbn\">$title</a></li>\n";
            }
            $output .= "</ul>\n";
        }
    }
    $output .= "</div>\n";
    return $output;
}

/**
 * Renders collection of books grouped by first letter of title, wrapped in boilerplate html
 */
function render_titles_page($debug=false)
{
    $books = get_book_titles();

    $content = '';
    $content .= output_alpha_header($books);
    $content .= output_book_titles($books);

    echo get_html_skel($content);

    if ($debug) {
        var_dump($books);
    }
}

/**
 * Renders renders details for a single book, wrapped in boilerplate html
 */
function render_detail_page($isbn, $debug=false)
{
    $book = get_book_details($isbn);
    $title = $book['title'];
    $content = '';

    // Book details
    $content .= "<h2>$title</h2>\n";
    $content .= "<i>ISBN: " . $book['isbn'] . "</i>\n";
    $content .= '<p style="width:500px">' . $book['description'] . "</p>\n";

    // Amazon link
    $amazon_link = "http://www.amazon.com/gp/product/$isbn";
    $content .= "<p><a href=\"$amazon_link\">Buy $title on Amazon</a></p>";

    // Back button
    $content .= '<input action="action" type="button" value="Back" onclick="history.go(-1);">';

    echo get_html_skel($content, "Details: $title");

    if ($debug) {
        var_dump($book);
    }
}

/**
 * Render the page
 */
function render_page($debug=false)
{
    $url = parse_url($_SERVER['REQUEST_URI']);
    //var_dump($url);

    // Extract 10-digit ISBN from URL either from path or query string
    $matches = array();
    if (isset($url['path']) && preg_match('/isbn:(\d{10})$/', $url['path'], $matches)
        || isset($url['query']) && preg_match('/isbn=(\d{10})$/', $url['query'], $matches)) {
        $isbn = $matches[1];
    }

    // Render either book details page or listing of all book titles
    if (isset($isbn)) {
        try {
            render_detail_page($isbn, $debug);
        } catch (Exception $e) {
            render_titles_page($debug);
        }
    } else {
        render_titles_page($debug);
    }
}
render_page($debug=false);