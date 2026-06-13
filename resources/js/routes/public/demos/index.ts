import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/demo-web',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DemoWebsiteController::index
 * @see app/Http/Controllers/DemoWebsiteController.php:15
 * @route '/demo-web'
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
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
export const embed = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: embed.url(args, options),
    method: 'get',
})

embed.definition = {
    methods: ["get","head"],
    url: '/demo-web/embed/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
embed.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return embed.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
embed.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: embed.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
embed.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: embed.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
    const embedForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: embed.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
        embedForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: embed.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DemoWebsiteController::embed
 * @see app/Http/Controllers/DemoWebsiteController.php:165
 * @route '/demo-web/embed/{id}'
 */
        embedForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: embed.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    embed.form = embedForm
const demos = {
    index: Object.assign(index, index),
embed: Object.assign(embed, embed),
}

export default demos