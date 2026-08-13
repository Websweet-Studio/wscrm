import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/blog',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BlogController::index
 * @see app/Http/Controllers/BlogController.php:13
 * @route '/blog'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
export const show = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/blog/{blogPost}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
show.url = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blogPost: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
            args = { blogPost: args.slug }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blogPost: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blogPost: typeof args.blogPost === 'object'
                ? args.blogPost.slug
                : args.blogPost,
                }

    return show.definition.url
            .replace('{blogPost}', parsedArgs.blogPost.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
show.get = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
show.head = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
    const showForm = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
        showForm.get = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
        showForm.head = (args: { blogPost: string | number | { slug: string | number } } | [blogPost: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
export const category = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: category.url(args, options),
    method: 'get',
})

category.definition = {
    methods: ["get","head"],
    url: '/blog/category/{category}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
category.url = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
            args = { category: args.slug }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.slug
                : args.category,
                }

    return category.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
category.get = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: category.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
category.head = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: category.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
    const categoryForm = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: category.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
        categoryForm.get = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: category.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
        categoryForm.head = (args: { category: string | number | { slug: string | number } } | [category: string | number | { slug: string | number } ] | string | number | { slug: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: category.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    category.form = categoryForm
const blog = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
category: Object.assign(category, category),
}

export default blog