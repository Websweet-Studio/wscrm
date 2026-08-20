import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/broadcast',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BroadcastController::index
 * @see app/Http/Controllers/Admin/BroadcastController.php:16
 * @route '/admin/broadcast'
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
* @see \App\Http\Controllers\Admin\BroadcastController::send
 * @see app/Http/Controllers/Admin/BroadcastController.php:24
 * @route '/admin/broadcast'
 */
export const send = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})

send.definition = {
    methods: ["post"],
    url: '/admin/broadcast',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BroadcastController::send
 * @see app/Http/Controllers/Admin/BroadcastController.php:24
 * @route '/admin/broadcast'
 */
send.url = (options?: RouteQueryOptions) => {
    return send.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BroadcastController::send
 * @see app/Http/Controllers/Admin/BroadcastController.php:24
 * @route '/admin/broadcast'
 */
send.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BroadcastController::send
 * @see app/Http/Controllers/Admin/BroadcastController.php:24
 * @route '/admin/broadcast'
 */
    const sendForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: send.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BroadcastController::send
 * @see app/Http/Controllers/Admin/BroadcastController.php:24
 * @route '/admin/broadcast'
 */
        sendForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: send.url(options),
            method: 'post',
        })
    
    send.form = sendForm
const broadcast = {
    index: Object.assign(index, index),
send: Object.assign(send, send),
}

export default broadcast