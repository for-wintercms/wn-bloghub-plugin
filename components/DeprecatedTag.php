<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Components;

use Log;

class DeprecatedTag extends PostsByTag
{
    
    /**
     * Declare Component Details
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'forwintercms.bloghub::lang.components.deprecated.tags_label',
            'description'   => 'forwintercms.bloghub::lang.components.deprecated.tags_comment'
        ];
    }

    /**
     * @inheritDoc
     */
    public function onRun()
    {
        Log::notice('The [bloghubTagArchive] component is deprecated, please use [bloghubPostsByTag] instead.');
        parent::onRun();
    }

}
