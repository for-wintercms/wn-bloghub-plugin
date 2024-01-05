<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;

class Base extends ComponentBase
{

    /**
     * Declare Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'forwintercms.bloghub::lang.components.base.label',
            'description'   => 'forwintercms.bloghub::lang.components.base.comment'
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
            'archiveAuthor' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.archive_author',
                'description'   => 'forwintercms.bloghub::lang.components.base.archive_author_comment',
                'type'          => 'dropdown',
                'default'       => 'blog/author'
            ],
            'archiveDate' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.archive_date',
                'description'   => 'forwintercms.bloghub::lang.components.base.archive_date_comment',
                'type'          => 'dropdown',
                'default'       => 'blog/date'
            ],
            'archiveTag' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.archive_tag',
                'description'   => 'forwintercms.bloghub::lang.components.base.archive_tag_comment',
                'type'          => 'dropdown',
                'default'       => 'blog/tag'
            ],
            'authorUseSlugOnly' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.author_slug',
                'description'   => 'forwintercms.bloghub::lang.components.base.author_slug_comment',
                'type'          => 'checkbox',
                'default'       => '0'
            ],
            'date404OnInvalid' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.date_invalid',
                'description'   => 'forwintercms.bloghub::lang.components.base.date_invalid_comment',
                'type'          => 'checkbox',
                'default'       => '1'
            ],
            'date404OnEmpty' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.date_empty',
                'description'   => 'forwintercms.bloghub::lang.components.base.date_empty_comment',
                'type'          => 'checkbox',
                'default'       => '1'
            ],
            'tagAllowMultiple' => [
                'title'         => 'forwintercms.bloghub::lang.components.base.tag_multiple',
                'description'   => 'forwintercms.bloghub::lang.components.base.tag_multiple_comment',
                'type'          => 'checkbox',
                'default'       => '0'
            ],
        ];
    }

    /**
     * Get available CMS Pages for Author Archive
     *
     * @return void
     */
    public function getArchiveAuthorOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Get available CMS Pages for Date Archive
     *
     * @return void
     */
    public function getArchiveDateOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Get available CMS Pages for Tag Archive
     *
     * @return void
     */
    public function getArchiveTagOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Initialize Component before any other bloghub component is executed.
     *
     * @return void
     */
    public function init()
    {
        $this->page['bloghub_config'] = $this->getProperties();
    }

}
