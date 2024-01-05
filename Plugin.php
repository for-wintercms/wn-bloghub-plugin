<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub;

use Backend;
use Event;
use Exception;
use Backend\Controllers\Users as BackendUsers;
use Backend\Facades\BackendAuth;
use Backend\Models\User as BackendUser;
use Backend\Widgets\Lists;
use Cms\Classes\Controller;
use Cms\Classes\Theme;
use Winter\Blog\Controllers\Posts;
use Winter\Blog\Models\Post;
use ForWinterCms\BlogHub\Behaviors\BlogHubBackendUserModel;
use ForWinterCms\BlogHub\Behaviors\BlogHubPostModel;
use ForWinterCms\BlogHub\Models\Comment;
use ForWinterCms\BlogHub\Models\MetaSettings;
use ForWinterCms\BlogHub\Models\Visitor;
use Symfony\Component\Yaml\Yaml;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    
    /**
     * Required Extensions
     *
     * @var array
     */
    public $require = [
        'Winter.Blog'
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'forwintercms.bloghub::lang.plugin.name',
            'description' => 'forwintercms.bloghub::lang.plugin.description',
            'author'      => 'ForWinterCms <info@rat.md>',
            'icon'        => 'icon-tags',
            'homepage'    => 'https://github.com/for-wintercms/wn-bloghub-plugin'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

        // Extend available sorting options
        Post::$allowedSortingOptions['forwn_bloghub_views asc'] = 'forwintercms.bloghub::lang.sorting.bloghub_views_asc';
        Post::$allowedSortingOptions['forwn_bloghub_views desc'] = 'forwintercms.bloghub::lang.sorting.bloghub_views_desc';
        Post::$allowedSortingOptions['forwn_bloghub_unique_views asc'] = 'forwintercms.bloghub::lang.sorting.bloghub_unique_views_asc';
        Post::$allowedSortingOptions['forwn_bloghub_unique_views desc'] = 'forwintercms.bloghub::lang.sorting.bloghub_unique_views_desc';
        Post::$allowedSortingOptions['forwn_bloghub_comments_count asc'] = 'forwintercms.bloghub::lang.sorting.bloghub_comments_count_asc';
        Post::$allowedSortingOptions['forwn_bloghub_comments_count desc'] = 'forwintercms.bloghub::lang.sorting.bloghub_comments_count_desc';
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        // Add side menuts to Winter.Blog
        Event::listen('backend.menu.extendItems', function($manager) {
            $manager->addSideMenuItems('Winter.Blog', 'blog', [
                'forwn_bloghub_tags' => [
                    'label'         => 'forwintercms.bloghub::lang.model.tags.label',
                    'icon'          => 'icon-tags',
                    'code'          => 'forwn-bloghub-tags',
                    'owner'         => 'ForWinterCms.BlogHub',
                    'url'           => Backend::url('forwintercms/bloghub/tags'),
                    'permissions'   => [
                        'forwn.bloghub.tags'
                    ]
                ],

                'forwn_bloghub_comments' => [
                    'label'         => 'forwintercms.bloghub::lang.model.comments.label',
                    'icon'          => 'icon-comments-o',
                    'code'          => 'forwn-bloghub-comments',
                    'owner'         => 'ForWinterCms.BlogHub',
                    'url'           => Backend::url('forwintercms/bloghub/comments'),
                    'counter'       => Comment::where('status', 'pending')->count(),
                    'permissions'   => [
                        'forwn.bloghub.comments'
                    ]
                ]
            ]);
        });

        // Collect (Unique) Views
        Event::listen('cms.page.end', function (Controller $ctrl) {
            $pageObject = $ctrl->getPageObject();
            if (property_exists($pageObject, 'vars')) {
                $post = $pageObject->vars['post'] ?? null;
            } else if (property_exists($pageObject, 'controller')) {
                $post = $pageObject->controller->vars['post'] ?? null;
            } else {
                $post = null;
            }
            if (empty($post)) {
                return;
            }

            $guest = BackendAuth::getUser() === null;
            $visitor = Visitor::currentUser();
            if (!$visitor->hasSeen($post)) {
                if ($guest) {
                    $post->forwn_bloghub_unique_views = is_numeric($post->forwn_bloghub_unique_views)? $post->forwn_bloghub_unique_views+1: 1;
                }
                $visitor->markAsSeen($post);
            }

            if ($guest) {
                $post->forwn_bloghub_views = is_numeric($post->forwn_bloghub_views)? $post->forwn_bloghub_views+1: 1;

                if (!empty($post->url)) {
                    $url = $post->url;
                    unset($post->url);
                }

                $post->save();

                if (isset($url)) {
                    $post->url = $url;
                }
            }
        });

        // Implement custom Models
        Post::extend(function($page) {
            if (!$page->isClassExtendedWith(BlogHubPostModel::class))
                $page->extendClassWith(BlogHubPostModel::class);
        });
        BackendUser::extend(function($backendUser) {
            if (!$backendUser->isClassExtendedWith(BlogHubBackendUserModel::class))
                $backendUser->extendClassWith(BlogHubBackendUserModel::class);
        });

        // Extend Form Fields on Posts Controller
        Posts::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof Post) {
                return;
            }

            // Add Comments Field
            $form->addSecondaryTabFields([
                'forwn_bloghub_comment_visible' => [
                    'tab'           => 'forwintercms.bloghub::lang.model.comments.label',
                    'type'          => 'switch',
                    'label'         => 'forwintercms.bloghub::lang.model.comments.post_visibility.label',
                    'comment'       => 'forwintercms.bloghub::lang.model.comments.post_visibility.comment',
                    'span'          => 'left',
                    'permissions'   => ['forwn.bloghub.comments.access_comments_settings']
                ],
                'forwn_bloghub_comment_mode' => [
                    'tab'           => 'forwintercms.bloghub::lang.model.comments.label',
                    'type'          => 'dropdown',
                    'label'         => 'forwintercms.bloghub::lang.model.comments.post_mode.label',
                    'comment'       => 'forwintercms.bloghub::lang.model.comments.post_mode.comment',
                    'showSearch'    => false,
                    'span'          => 'left',
                    'options'       => [
                        'open' => 'forwintercms.bloghub::lang.model.comments.post_mode.open',
                        'restricted' => 'forwintercms.bloghub::lang.model.comments.post_mode.restricted',
                        'private' => 'forwintercms.bloghub::lang.model.comments.post_mode.private',
                        'closed' => 'forwintercms.bloghub::lang.model.comments.post_mode.closed',
                    ],
                    'permissions'   => ['forwn.bloghub.comments.access_comments_settings']
                ],
            ]);

            // Build Meta Map
            $meta = $model->forwn_bloghub_meta->mapWithKeys(fn ($item, $key) => [$item['name'] => $item['value']])->all();
            $model->forwn_bloghub_meta_temp = $meta;

            // Add Tags Field
            $form->addSecondaryTabFields([
                'forwn_bloghub_tags' => [
                    'label'         => 'forwintercms.bloghub::lang.model.tags.label',
                    'mode'          => 'relation',
                    'tab'           => 'winter.blog::lang.post.tab_categories',
                    'type'          => 'taglist',
                    'nameFrom'      => 'slug',
                    'permissions'   => ['forwn.bloghub.tags']
                ]
            ]);

            // Custom Meta Data
            $config = [];
            $settings = MetaSettings::get('meta_data', []);
            if (is_array($settings)) {
                foreach (MetaSettings::get('meta_data', []) AS $item) {
                    try {
                        $temp = Yaml::parse($item['config']);
                    } catch (Exception $e) {
                        $temp = null;
                    }
                    if (empty($temp)) {
                        continue;
                    }

                    $config[$item['name']] = $temp;
                    $config[$item['name']]['type'] = $item['type'];

                    // Add Label if missing
                    if (empty($config[$item['name']]['label'])) {
                        $config[$item['name']]['label'] = $item['name'];
                    }
                }
            }
            $config = array_merge($config, Theme::getActiveTheme()->getConfig()['forwn.bloghub']['post'] ?? []);

            // Add Custom Meta Fields
            if (!empty($config)) {
                foreach ($config AS $key => $value) {
                    if (empty($value['tab'])) {
                        $value['tab'] = 'forwintercms.bloghub::lang.settings.defaultTab';
                    }
                    $form->addSecondaryTabFields([
                        "forwn_bloghub_meta_temp[$key]" => array_merge($value, [
                            'value' => $meta[$key] ?? '',
                            'default' => $meta[$key] ?? ''
                        ])
                    ]);
                }
            }
        });

        // Extend List Columns on Posts Controller
        Posts::extendListColumns(function (Lists $list, $model) {
            if (!$model instanceof Post) {
                return;
            }
    
            $list->addColumns([
                'forwn_bloghub_views' => [
                    'label' => 'forwintercms.bloghub::lang.model.visitors.views',
                    'type' => 'number',
                    'select' => 'concat(winter_blog_posts.forwn_bloghub_views, " / ", winter_blog_posts.forwn_bloghub_unique_views)',
                    'align' => 'left'
                ]
            ]);
        });

        // Add Posts Filter Scope
        Posts::extendListFilterScopes(function ($filter) {
            $filter->addScopes([
                'forwn_bloghub_tags' => [
                    'label' => 'forwintercms.bloghub::lang.model.tags.label',
                    'modelClass' => 'ForWinterCms\BlogHub\Models\Tag',
                    'nameFrom' => 'slug',
                    'scope' => 'FilterTags'
                ]
            ]);
        });
        
        // Extend Backend Users Controller
        BackendUsers::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof BackendUser) {
                return;
            }
    
            // Add Display Name
            $form->addTabFields([
                'forwn_bloghub_display_name' => [
                    'label'         => 'forwintercms.bloghub::lang.model.users.displayName',
                    'description'   => 'forwintercms.bloghub::lang.model.users.displayNameDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'text',
                    'span'          => 'left'
                ],
                'forwn_bloghub_author_slug' => [
                    'label'         => 'forwintercms.bloghub::lang.model.users.authorSlug',
                    'description'   => 'forwintercms.bloghub::lang.model.users.authorSlugDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'text',
                    'span'          => 'right'
                ],
                'forwn_bloghub_about_me' => [
                    'label'         => 'forwintercms.bloghub::lang.model.users.aboutMe',
                    'description'   => 'forwintercms.bloghub::lang.model.users.aboutMeDescription',
                    'tab'           => 'backend::lang.user.account',
                    'type'          => 'textarea',
                ]
            ]);
        });
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            \ForWinterCms\BlogHub\Components\Base::class => 'bloghubBase',
            \ForWinterCms\BlogHub\Components\PostsByAuthor::class => 'bloghubPostsByAuthor',
            \ForWinterCms\BlogHub\Components\PostsByCommentCount::class => 'bloghubPostsByCommentCount',
            \ForWinterCms\BlogHub\Components\PostsByDate::class => 'bloghubPostsByDate',
            \ForWinterCms\BlogHub\Components\PostsByTag::class => 'bloghubPostsByTag',
            \ForWinterCms\BlogHub\Components\CommentList::class => 'bloghubCommentList',
            \ForWinterCms\BlogHub\Components\CommentSection::class => 'bloghubCommentSection',
            \ForWinterCms\BlogHub\Components\Tags::class => 'bloghubTags',

            // Deprecated Components
            \ForWinterCms\BlogHub\Components\DeprecatedAuthors::class => 'bloghubAuthorArchive',
            \ForWinterCms\BlogHub\Components\DeprecatedDates::class => 'bloghubDateArchive',
            \ForWinterCms\BlogHub\Components\DeprecatedTag::class => 'bloghubTagArchive',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'forwn.bloghub.comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.access_comments',
                'comment' => 'forwintercms.bloghub::lang.permissions.access_comments_comment',
            ],
            'forwn.bloghub.comments.access_comments_settings' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.manage_post_settings'
            ],
            'forwn.bloghub.comments.moderate_comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.moderate_comments'
            ],
            'forwn.bloghub.comments.delete_comments' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.delete_commpents'
            ],
            'forwn.bloghub.tags' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.access_tags',
                'comment' => 'forwintercms.bloghub::lang.permissions.access_tags_comment',
            ],
            'forwn.bloghub.tags.promoted' => [
                'tab'   => 'winter.blog::lang.blog.tab',
                'label' => 'forwintercms.bloghub::lang.permissions.promote_tags'
            ]
        ];
    }

    /**
     * Registers backend navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }

    /**
     * Registers settings navigation items for this plugin.
     *
     * @return array
     */
    public function registerSettings()
    {
        return [
            'forwn_bloghub_config' => [
                'label'         => 'forwintercms.bloghub::lang.settings.config.label',
                'description'   => 'forwintercms.bloghub::lang.settings.config.description',
                'category'      => 'winter.blog::lang.blog.menu_label',
                'icon'          => 'icon-pencil-square-o',
                'class'         => 'ForWinterCms\BlogHub\Models\BlogHubSettings',
                'order'         => 500,
                'keywords'      => 'blog post meta data',
                'permissions'   => ['winter.blog.manage_settings'],
                'size'          => 'adaptive'
            ],
            'forwn_bloghub_meta' => [
                'label'         => 'forwintercms.bloghub::lang.settings.meta.label',
                'description'   => 'forwintercms.bloghub::lang.settings.meta.description',
                'category'      => 'winter.blog::lang.blog.menu_label',
                'icon'          => 'icon-list-ul',
                'class'         => 'ForWinterCms\BlogHub\Models\MetaSettings',
                'order'         => 500,
                'keywords'      => 'blog post meta data',
                'permissions'   => ['winter.blog.manage_settings'],
                'size'          => 'adaptive'
            ]
        ];
    }

    /**
     * Registers any report widgets provided by this package.
     *
     * @return array
     */
    public function registerReportWidgets()
    {
        return [
            \ForWinterCms\BlogHub\ReportWidgets\CommentsList::class => [
                'label' => 'forwintercms.bloghub::lang.widgets.comments_list.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts',
                    'forwn.bloghub.comments'
                ]
            ],
            \ForWinterCms\BlogHub\ReportWidgets\PostsList::class => [
                'label' => 'forwintercms.bloghub::lang.widgets.posts_list.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts'
                ]
            ],
            \ForWinterCms\BlogHub\ReportWidgets\PostsStatistics::class => [
                'label' => 'forwintercms.bloghub::lang.widgets.posts_statistics.label',
                'context' => 'dashboard',
                'permission' => [
                    'winter.blog.access_other_posts'
                ]
            ],
        ];
    }

}
