import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import demos from './demos'
import domains from './domains'
import username from './username'
import customer from './customer'
/**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
export const oembed = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: oembed.url(options),
    method: 'get',
})

oembed.definition = {
    methods: ["get","head"],
    url: '/api/oembed',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
oembed.url = (options?: RouteQueryOptions) => {
    return oembed.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
oembed.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: oembed.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
oembed.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: oembed.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
    const oembedForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: oembed.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
        oembedForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: oembed.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DemoWebsiteController::oembed
 * @see app/Http/Controllers/DemoWebsiteController.php:250
 * @route '/api/oembed'
 */
        oembedForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: oembed.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    oembed.form = oembedForm
const api = {
    demos: Object.assign(demos, demos),
oembed: Object.assign(oembed, oembed),
domains: Object.assign(domains, domains),
username: Object.assign(username, username),
customer: Object.assign(customer, customer),
}

export default api