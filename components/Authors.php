<?php declare(strict_types=1);

namespace RatMD\BlogHub\Components;

use Backend\Models\User;
use RainLab\Blog\Components\Posts;
use RainLab\Blog\Models\Post;

class Authors extends Posts
{

    /**
     * The post list filtered by this tag model
     *
     * @var Model
     */
    public $author;

    /**
     * Component Details
     *
     * @return void
     */
    public function componentDetails()
    {
        return [
            'name'          => 'ratmd.bloghub::lang.components.authors_title',
            'description'   => 'ratmd.bloghub::lang.components.authors_description'
        ];
    }

    /**
     * Component Properties
     *
     * @return void
     */
    public function defineProperties()
    {
        $properties = parent::defineProperties();
        $properties['authorFilter'] = [
            'title'       => 'ratmd.bloghub::lang.components.author_filter_title',
            'description' => 'ratmd.bloghub::lang.components.author_filter_description',
            'type'        => 'string',
            'default'     => '',
        ];
        return $properties;
    }

    /**
     * Prepare Variables
     *
     * @return void
     */
    protected function prepareVars()
    {
        $this->author = $this->page['author'] = $this->loadAuthor();
    }

    /**
     * List Posts
     *
     * @return mixed
     */
    protected function listPosts()
    {
        $author = $this->author->id;
        $category = $this->category ? $this->category->id : null;
        $categorySlug = $this->category ? $this->category->slug : null;

        /*
         * List all the posts, eager load their categories
         */
        $isPublished = !parent::checkEditor();

        $posts = Post::with(['categories', 'featured_images', 'bloghub_tags'])
            ->where('user_id', '=', $author)
            ->listFrontEnd([
                'page'             => $this->property('pageNumber'),
                'sort'             => $this->property('sortOrder'),
                'perPage'          => $this->property('postsPerPage'),
                'search'           => trim(input('search') ?? ''),
                'category'         => $category,
                'published'        => $isPublished,
                'exceptPost'       => is_array($this->property('exceptPost'))
                    ? $this->property('exceptPost')
                    : preg_split('/,\s*/', $this->property('exceptPost'), -1, PREG_SPLIT_NO_EMPTY),
                'exceptCategories' => is_array($this->property('exceptCategories'))
                    ? $this->property('exceptCategories')
                    : preg_split('/,\s*/', $this->property('exceptCategories'), -1, PREG_SPLIT_NO_EMPTY),
            ]);

        /*
         * Add a "url" helper attribute for linking to each post and category
         */
        $posts->each(function($post) use ($categorySlug) {
            $post->setUrl($this->postPage, $this->controller, ['category' => $categorySlug]);

            $post->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $posts;
    }
    
    /**
     * Load Author
     *
     * @return User|null
     */
    protected function loadAuthor()
    {
        if (!$username = $this->property('authorFilter')) {
            return null;
        }

        $user = User::where('login', $username)->first();
        return $user ?: null;
    }

}
