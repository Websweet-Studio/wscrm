import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/credits',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\CreditController::index
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:18
 * @route '/admin/ai/credits'
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
* @see \App\Http\Controllers\Admin\Ai\CreditController::adjust
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:48
 * @route '/admin/ai/credits/adjust'
 */
export const adjust = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(options),
    method: 'post',
})

adjust.definition = {
    methods: ["post"],
    url: '/admin/ai/credits/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::adjust
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:48
 * @route '/admin/ai/credits/adjust'
 */
adjust.url = (options?: RouteQueryOptions) => {
    return adjust.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\CreditController::adjust
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:48
 * @route '/admin/ai/credits/adjust'
 */
adjust.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\CreditController::adjust
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:48
 * @route '/admin/ai/credits/adjust'
 */
    const adjustForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adjust.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\CreditController::adjust
 * @see app/Http/Controllers/Admin/Ai/CreditController.php:48
 * @route '/admin/ai/credits/adjust'
 */
        adjustForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adjust.url(options),
            method: 'post',
        })
    
    adjust.form = adjustForm
const credits = {
    index: Object.assign(index, index),
adjust: Object.assign(adjust, adjust),
}

export default credits