<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Components;

use Log;

class DeprecatedAuthors extends PostsByAuthor
{
    /**
     * Declare Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'forwintercms.bloghub::lang.components.deprecated.authors_label',
            'description'   => 'forwintercms.bloghub::lang.components.deprecated.authors_comment'
        ];
    }

    /**
     * @inheritDoc
     */
    public function onRun()
    {
        Log::notice('The [bloghubAuthorArchive] component is deprecated, please use [bloghubPostsByAuthor] instead.');
        parent::onRun();
    }

}
