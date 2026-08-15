import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/transactions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\TransactionController::index
 * @see app/Http/Controllers/Admin/Ai/TransactionController.php:13
 * @route '/admin/ai/transactions'
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
const transactions = {
    index: Object.assign(index, index),
}

export default transactions