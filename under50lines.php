<?php
/**
 * Guidance Coding Challenge
 * Author: Ted Toporkov
 * Date: 3/10/2016
 */

/**
 * Get book titles from SQLite db grouped by first letter of book title
 */
function get_book_titles()
{
    $db      = new SQLite3('books.db');
    $sql     = "SELECT SUBSTR(title, 1, 1) AS first_letter, * FROM books";
    $books   = array();
    $results = $db->query($sql);

    // Group results from books by first letter of title
    while ($row = $results->fetchArray($mode = SQLITE3_ASSOC)) {
        $books[$row['first_letter']][] = $row;
    }
    return $books;
}

/**
 * Output alphabet links header
 */
function output_alpha_header(&$books)
{
    echo "<div><h2>";
    foreach (range('A', 'Z') as $letter) {
        if (array_key_exists($letter, $books)) {
            echo "<a href=\"#$letter\">$letter<a/> ";
        } else {
            echo $letter . " ";
        }
    }
    echo "</h2><div>\n";
}

/**
 * Output alphabet links header
 */
function output_book_titles(&$books)
{
    // Output book titles & links grouped by first letter of title
    echo "<div>\n";
    foreach(range('A', 'Z') as $letter) {
        if (array_key_exists($letter, $books)) {

            echo "<h3 id=\"$letter\">$letter</h3>\n";

            echo "<ul>\n";
            foreach($books[$letter] as $book) {
                echo "<li><a href=\"index.php?isbn={$book['isbn']}\">{$book['title']}</a></li>\n";
            }
            echo "</ul>\n";
        }
    }
    echo "</div>\n";
}

// Render page
$books = get_book_titles();
output_alpha_header($books);
output_book_titles($books);
