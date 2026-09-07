<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class BlogContentSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'div', 'span', 'br', 'hr', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'mark',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'figure', 'figcaption',
    ];

    private const REMOVE_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'template'];

    public static function sanitize(string $content): string
    {
        if (! preg_match('/<\/?[a-z][^>]*>/i', $content)) {
            return $content;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $encoded = mb_encode_numericentity($content, [0x80, 0x10FFFF, 0, 0xFFFFFF], 'UTF-8');

        $document->loadHTML(
            '<!DOCTYPE html><html><body><div id="blog-content-root">' . $encoded . '</div></body></html>',
            LIBXML_HTML_NODEFDTD | LIBXML_NONET,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('blog-content-root');

        if (! $root) {
            return '';
        }

        self::sanitizeChildren($root);

        $result = '';

        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private static function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($node);
                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
                $parent->removeChild($node);
                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::sanitizeChildren($node);

                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }

                $parent->removeChild($node);
                continue;
            }

            self::sanitizeAttributes($node);
            self::sanitizeChildren($node);
        }
    }

    private static function sanitizeAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            $allowed = match ($name) {
                'href' => $element->tagName === 'a' && self::safeUrl($value, true),
                'src' => $element->tagName === 'img' && self::safeUrl($value, false),
                'alt', 'title' => in_array($element->tagName, ['a', 'img'], true),
                'class' => self::safeClasses($value) !== '',
                'colspan', 'rowspan' => in_array($element->tagName, ['td', 'th'], true) && ctype_digit($value),
                'start' => $element->tagName === 'ol' && ctype_digit($value),
                'reversed' => $element->tagName === 'ol',
                'style' => ($style = self::sanitizeStyle($value)) !== '',
                default => false,
            };

            if (! $allowed) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if ($name === 'style') {
                $element->setAttribute('style', $style);
            }

            if ($name === 'class') {
                $element->setAttribute('class', self::safeClasses($value));
            }
        }

        if ($element->tagName === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }

        if ($element->tagName === 'img') {
            $element->setAttribute('loading', 'lazy');
        }
    }

    private static function safeUrl(string $url, bool $allowMail): bool
    {
        if ($url === '' || preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        if ($allowMail && str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, $allowMail ? ['http', 'https', 'mailto'] : ['http', 'https'], true);
    }

    private static function sanitizeStyle(string $style): string
    {
        $safe = [];

        foreach (explode(';', $style) as $declaration) {
            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $property = strtolower(trim($property));
            $value = strtolower(trim($value));

            $allowed = match ($property) {
                'color', 'background-color', 'border-color' => (bool) preg_match('/^(#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)|hsl\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*\)|[a-z]{3,20})$/i', $value),
                'text-align' => in_array($value, ['left', 'center', 'right', 'justify'], true),
                'font-size', 'width', 'height', 'padding', 'border-width' => (bool) preg_match('/^\d{1,3}(?:\.\d{1,2})?(px|em|rem|%)$/', $value),
                'font-family' => (bool) preg_match('/^[a-z0-9\s,\-\'"]{2,100}$/i', $value),
                'border-style' => in_array($value, ['solid', 'dotted', 'dashed', 'double', 'groove', 'ridge'], true),
                default => false,
            };

            if ($allowed) {
                $safe[] = $property . ': ' . $value;
            }
        }

        return implode('; ', $safe);
    }

    private static function safeClasses(string $classes): string
    {
        return implode(' ', array_filter(
            preg_split('/\s+/', $classes) ?: [],
            fn (string $class) => (bool) preg_match('/^(?:image(?:_resized|-style-[a-z-]+)?|table|text-(?:tiny|small|big|huge)|marker-(?:yellow|green|pink|blue)|pen-(?:red|green))$/', $class),
        ));
    }
}
