import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/domains',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DomainPriceController::index
 * @see app/Http/Controllers/DomainPriceController.php:39
 * @route '/domains'
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
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
export const search = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

search.definition = {
    methods: ["get","head"],
    url: '/domains/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
    const searchForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: search.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
        searchForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: search.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\DomainPriceController::search
 * @see app/Http/Controllers/DomainPriceController.php:54
 * @route '/domains/search'
 */
        searchForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: search.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    search.form = searchForm
const domains = {
    index: Object.assign(index, index),
search: Object.assign(search, search),
}

export default domains