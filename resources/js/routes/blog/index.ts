import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../wayfinder'
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
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
export const show = (args: { blogPost: string | { slug: string } } | [blogPost: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
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
show.url = (args: { blogPost: string | { slug: string } } | [blogPost: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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
show.get = (args: { blogPost: string | { slug: string } } | [blogPost: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BlogController::show
 * @see app/Http/Controllers/BlogController.php:82
 * @route '/blog/{blogPost}'
 */
show.head = (args: { blogPost: string | { slug: string } } | [blogPost: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
export const category = (args: { category: string | { slug: string } } | [category: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
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
category.url = (args: { category: string | { slug: string } } | [category: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
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
category.get = (args: { category: string | { slug: string } } | [category: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: category.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BlogController::category
 * @see app/Http/Controllers/BlogController.php:113
 * @route '/blog/category/{category}'
 */
category.head = (args: { category: string | { slug: string } } | [category: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: category.url(args, options),
    method: 'head',
})
const blog = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
category: Object.assign(category, category),
}

export default blog