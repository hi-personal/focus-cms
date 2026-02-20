<?php

namespace App\Services;

use Embed\Embed;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Embed\Bridge\OscaroteroEmbedAdapter;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\MarkdownConverter;

use Illuminate\Support\Str;
use App\Models\PostImage;


/**
 * CustomMarkdownService
 */
class CustomMarkdownService
{
    /**
     * converter
     *
     * @var mixed
     */
    protected $converter;

    /**
     * Method __construct
     *
     * @return void
     */
    public function __construct()
    {
        $config = [
            'attributes' => [
                'allow' => ['id', 'class', 'data', 'align'],
            ],
            'autolink' => [
                'allowed_protocols' => ['http', 'https'], // defaults to ['https', 'http', 'ftp']
                'default_protocol' => 'https', // defaults to 'http'
            ],
            'default_attributes' => [
                Heading::class => [
                    'class' => static function (Heading $node) {
                        if ($node->getLevel() === 1) {
                            return 'title-main';
                        } else {
                            return null;
                        }
                    },
                ],
                Table::class => [
                    'class' => 'table',
                ],
                Paragraph::class => [
                    'class' => [],
                ],
                Link::class => [
                    'class' => 'btn btn-link',
                    'target' => '_blank',
                    'rel' => 'noopener noreferrer',
                ],
            ],
            'embed' => [
                'adapter' => new OscaroteroEmbedAdapter(),
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com'],
                'fallback' => 'link',
            ],
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => 'content',
                'apply_id_to_heading' => false,
                'heading_class' => '',
                'fragment_prefix' => 'content',
                'insert' => 'before',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'title' => 'Permalink',
                'symbol' => '', // HeadingPermalinkRenderer::DEFAULT_SYMBOL,
                'aria_hidden' => true,
            ],
            'table_of_contents' => [
                'html_class' => 'table-of-contents',
                'position' => 'top',
                'style' => 'bullet',
                'min_heading_level' => 1,
                'max_heading_level' => 6,
                'normalize' => 'relative',
                'placeholder' => null,
            ],
            'smartpunct' => [
                'double_quote_opener' => '"',
                'double_quote_closer' => '"',
                'single_quote_opener' => "'",
                'single_quote_closer' => "'",
            ],
            'table' => [
                'wrap' => [
                    'enabled' => false,
                    'tag' => 'div',
                    'attributes' => [],
                ],
                'alignment_attributes' => [
                    'left'   => ['align' => 'left'],
                    'center' => ['align' => 'center'],
                    'right'  => ['align' => 'right'],
                ],
            ],
            'disallowed_raw_html' => [
                'disallowed_tags' => ['title', 'textarea', 'style', 'xmp',
                //'iframe',
                 'noembed', 'noframes', 'script', 'plaintext'],
            ],
            'footnote' => [
                'backref_class'      => 'footnote-backref',
                'backref_symbol'     => '↩',
                'container_add_hr'   => false,
                'container_class'    => 'footnotes',
                'ref_class'          => 'footnote-ref',
                'ref_id_prefix'      => 'fnref-',
                'footnote_class'     => 'footnote',
                'footnote_id_prefix' => 'footnote-',
            ],
        ];

        // Create a new Environment with the core extension
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new DefaultAttributesExtension());
        $environment->addExtension(new DescriptionListExtension());
        $environment->addExtension(new DisallowedRawHtmlExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());

        $environment->addExtension(new AttributesExtension());
        //$environment->addExtension(new AutolinkExtension());
        //$environment->addExtension(new EmbedExtension());
        $environment->addExtension(new FootnoteExtension());
        //$environment->addExtension(new HeadingPermalinkExtension());
        //$environment->addExtension(new TableOfContentsExtension());
        //$environment->addExtension(new SmartPunctExtension());
        $environment->addExtension(new TaskListExtension());

        // Instantiate the converter engine and start converting some Markdown!
        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Method text
     *
     * @param $html $html [explicite description]
     *
     * @return void
     */
    public function text($html, $nl2br = true)
    {
        $html = $this->converter->convert($html)->getContent();
        //$html = htmlspecialchars_decode($html, ENT_QUOTES);


        $prePlaceholders = [];
        $html = preg_replace_callback(
            '/<pre[^>]*>.*?<\/pre>/is',
            function($matches) use (&$prePlaceholders) {
                $placeholder = '[[PRE' . count($prePlaceholders) . ']]';
                $prePlaceholders[$placeholder] = $matches[0];
                return $placeholder;
            },
            $html
        );

        if ($nl2br == true) {
            $html = preg_replace_callback(
                '/<p[^>]*>(.*?)<\/p>/is',
                function($matches) {
                    $content = nl2br($matches[1]);
                    return "<p>{$content}</p>";
                },
                $html
            );
        }

        $html = str_replace(array_keys($prePlaceholders), array_values($prePlaceholders), $html);

        return $html;
    }
}