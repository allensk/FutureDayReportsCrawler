<?php

/**
 * Minify html
 * @param $html html content
 */
function removeHtmlSpace($html)
{
    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );

    return preg_replace($search, $replace, $html);
}

/**
 * Minify html and striple attributes.
 */
function tidyHtmlTradeData($htmlFileName, $outFileName) {

    $html = file_get_contents($htmlFileName);

    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

    // Remove form tag
    for ($i = 0; ; ++$i) {
        $eles = $dom->getElementsByTagName('form');
        foreach ($eles as $ele) {
            $ele->parentNode->removeChild($ele);
        }
        
        if ($eles->length == 0)
            break;
    }

    // Remove a tag
    for ($i = 0; ; ++$i) {
        $eles = $dom->getElementsByTagName('a');
        foreach ($eles as $ele) {
            $ele->parentNode->removeChild($ele);
        }
        
        if ($eles->length == 0)
            break;
    }

    // Only save the data table
    $ele = $dom->getElementById('waitBody');
    $html = $dom->saveHtml($ele);

    // Remove elements' attributes for preserve.
    $pattern = '/\w+="[\w-:;\/.%#\s]+"/';
    $count = -1;
    $html = preg_replace($pattern, '', $html, -1, $count);

    $html = removeHtmlSpace($html);

    $html = str_replace('td nowrap', 'td', $html);
    $html = str_replace(' >', '>', $html);
    $html = str_replace('<table>',
        '<table style="table-layout: auto;" border="1" width="100%">',
        $html);

    file_put_contents($outFileName, $html);
}