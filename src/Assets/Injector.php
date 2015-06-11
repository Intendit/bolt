<?php
namespace Bolt\Assets;

use Bolt\Helpers\Str;

/**
 * Class for matching HTML elements and injecting text.
 *
 * @author Bob den Otter <bob@twokings.nl>
 * @author Gawain Lynch <gawain.lynch@gmaill.com>
 */
class Injector
{

    /**
     * Get a map of function names to locations.
     *
     * @return array
     */
    public function getMap()
    {
        return [
            Target::END_OF_HEAD      => 'headTagEnd',
            Target::AFTER_HEAD_JS    => 'headTagEnd', // same as end of head because we cheat a little
            Target::AFTER_HEAD_CSS   => 'headTagEnd', // same as end of head because we cheat a little
            Target::AFTER_HEAD_META  => 'headTagEnd', // same as end of head because meta tags are unordered

            Target::BEFORE_CSS       => 'cssTagsBefore',
            Target::BEFORE_JS        => 'jsTagsBefore',
            Target::AFTER_META       => 'metaTagsAfter',
            Target::AFTER_CSS        => 'cssTagsAfter',
            Target::AFTER_JS         => 'jsTagsAfter',

            Target::START_OF_HEAD    => 'headTagStart',
            Target::BEFORE_HEAD_JS   => 'headTagStart', // same as start of head because we cheat a little
            Target::BEFORE_HEAD_CSS  => 'headTagStart', // same as start of head because we cheat a little
            Target::BEFORE_HEAD_META => 'headTagStart', // same as start of head because meta tags are unordered

            Target::START_OF_BODY    => 'bodyTagStart',
            Target::BEFORE_BODY_JS   => 'bodyTagStart', // same as start of body because we cheat a little
            Target::BEFORE_BODY_CSS  => 'bodyTagStart', // same as start of body because we cheat a little

            Target::END_OF_BODY      => 'bodyTagEnd',
            Target::AFTER_BODY_JS    => 'bodyTagEnd',   // same as end of body because we cheat a little
            Target::AFTER_BODY_CSS   => 'bodyTagEnd',   // same as end of body because we cheat a little

            Target::END_OF_HTML      => 'htmlTagEnd',
        ];
    }

    /**
     * Helper function to insert some HTML into the start of the head section of
     * an HTML page, right after the <head> tag.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function headTagStart($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<head', true, false)) {
            $replacement = sprintf("%s\n%s\t%s", $matches[0], $matches[1], $addedHtml);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the head section of an HTML
     * page, right before the </head> tag.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function headTagEnd($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '</head', false, false)) {
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $addedHtml, $matches[0]);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the start of the head section of
     * an HTML page, right after the <body> tag.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function bodyTagStart($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<body', true, false)) {
            $replacement = sprintf("%s\n%s\t%s", $matches[0], $matches[1], $addedHtml);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the body section of an HTML
     * page, right before the </body> tag.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function bodyTagEnd($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '</body', false, false)) {
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $addedHtml, $matches[0]);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the html section of an HTML
     * page, right before the </html> tag.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function htmlTagEnd($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '</html', false, false)) {
            $replacement = sprintf("%s\t%s\n%s", $matches[1], $addedHtml, $matches[0]);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the head section of an HTML page.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function metaTagsAfter($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<meta', true, true)) {
            $last = count($matches[0]) - 1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $addedHtml);

            return Str::replaceFirst($matches[0][$last], $replacement, $rawHtml);
        }

        return $this->headTagEnd($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML into the head section of an HTML page.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function cssTagsAfter($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<link', true, true)) {
            $last = count($matches[0]) - 1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $addedHtml);

            return Str::replaceFirst($matches[0][$last], $replacement, $rawHtml);
        }

        return $this->headTagEnd($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML before the first CSS include in the page.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function cssTagsBefore($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<link', true, false)) {
            $replacement = sprintf("%s%s\n%s\t%s", $matches[1], $addedHtml, $matches[0], $matches[1]);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML before the first javascript include in the page.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    public function jsTagsBefore($rawHtml, $addedHtml)
    {
        if ($matches = $this->getMatches($rawHtml, '<script', true, false)) {
            $replacement = sprintf("%s%s\n%s\t%s", $matches[1], $addedHtml, $matches[0], $matches[1]);

            return Str::replaceFirst($matches[0], $replacement, $rawHtml);
        }

        return $this->tagSoup($rawHtml, $addedHtml);
    }

    /**
     * Helper function to insert some HTML after the last javascript include.
     * First in the head section, but if there is no script in the head, place
     * it anywhere.
     *
     * @param string  $rawHtml
     * @param string  $addedHtml
     * @param boolean $insidehead
     *
     * @return string
     */
    public function jsTagsAfter($rawHtml, $addedHtml, $insidehead = false)
    {
        if ($insidehead) {
            $pos = strpos($rawHtml, '</head>');
            $context = substr($rawHtml, 0, $pos);
        } else {
            $context = $rawHtml;
        }

        // This match tag is a unique case
        if ($matches = $this->getMatches($context, '(.*)</script>', false, true)) {
            // Attempt to insert it after the last <script> tag within context, matching indentation.
            $last = count($matches[0]) - 1;
            $replacement = sprintf("%s\n%s%s", $matches[0][$last], $matches[1][$last], $addedHtml);

            return Str::replaceFirst($matches[0][$last], $replacement, $rawHtml);
        } elseif ($insidehead) {
            // Second attempt: entire document
            return $this->jsTagsAfter($rawHtml, $addedHtml, false);
        }

        return $this->headTagEnd($rawHtml, $addedHtml);
    }

    /**
     * Get a set of matches.
     *
     * @param string  $rawHtml        The original HTML
     * @param string  $htmlTag        HTML tag fragment we're matching, e.g. '<head' or '</head'
     * @param boolean $matchRemainder TRUE matches the remainder of the line, not just the tag - (.*)
     * @param boolean $matchAll       TRUE returns all matched instances - preg_match_all()
     *
     * @return string[]
     */
    private function getMatches($rawHtml, $htmlTag, $matchRemainder, $matchAll)
    {
        $matches = null;
        $matchRemainder = $matchRemainder ? '(.*)' : '';
        $regex = sprintf("~^([ \t]*)%s%s~mi", $htmlTag, $matchRemainder);

        if ($matchAll && preg_match_all($regex, $rawHtml, $matches)) {
            return $matches;
        } elseif (!$matchAll && preg_match($regex, $rawHtml, $matches)) {
            return $matches;
        }
    }

    /**
     * Since we're serving tag soup, just append the tag to the HTML we're given.
     *
     * @param string $rawHtml
     * @param string $addedHtml
     *
     * @return string
     */
    private function tagSoup($rawHtml, $addedHtml)
    {
        return "$rawHtml$addedHtml\n";
    }
}
