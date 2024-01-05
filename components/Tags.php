<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use ForWinterCms\BlogHub\Models\Tag as TagModel;

class Tags extends ComponentBase
{
    /**
     * A collection of tags to display
     *
     * @var Collection
     */
    public $tags;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $tagPage;

    /**
     * Component Details
     *
     * @return void
     */
    public function componentDetails()
    {
        return [
            'name'          => 'forwintercms.bloghub::lang.components.tags.label',
            'description'   => 'forwintercms.bloghub::lang.components.tags.comment'
        ];
    }

    /**
     * Component Properties
     *
     * @return void
     */
    public function defineProperties()
    {
        return [
            'tagPage' => [
                'title'             => 'forwintercms.bloghub::lang.components.tags.tags_page',
                'description'       => 'forwintercms.bloghub::lang.components.tags.tags_page_comment',
                'type'              => 'dropdown',
                'default'           => 'blog/tag',
                'group'             => 'winter.blog::lang.settings.group_links',
            ],
            'onlyPromoted' => [
                'title'             => 'forwintercms.bloghub::lang.components.tags.only_promoted',
                'description'       => 'forwintercms.bloghub::lang.components.tags.only_promoted_comment',
                'type'              => 'checkbox',
                'default'           => '0'
            ],
            'amount' => [
                'title'             => 'forwintercms.bloghub::lang.components.tags.amount',
                'description'       => 'forwintercms.bloghub::lang.components.tags.amount_comment',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'forwintercms.bloghub::lang.components.tags.amount_validation',
                'default'           => '5',
            ]
        ];
    }

    /**
     * Get Tag Page Option
     *
     * @return void
     */
    public function getTagPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Run
     *
     * @return void
     */
    public function onRun()
    {
        $this->tagPage = $this->page['tagPage'] = $this->property('tagPage');
        $this->tags = $this->page['tags'] = $this->listTags();
    }

    /**
     * Load popular tags
     * 
     * @return mixed
     */
    protected function listTags()
    {
        $query = TagModel::withCount(['posts_count'])
            ->where('posts_count_count', '>', 0)
            ->orderBy('posts_count_count', 'desc');

        if ($this->property('onlyPromoted') === '1') {
            $query->where('promote', '1');
        }

        $amount = intval($this->property('amount'));
        $query->limit(5);

        return $query->get()->each(fn ($tag) => $tag->setUrl($this->tagPage, $this->controller));
    }

}
