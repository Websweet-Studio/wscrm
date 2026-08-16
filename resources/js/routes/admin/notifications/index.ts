import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\NotificationController::index
 * @see app/Http/Controllers/Admin/NotificationController.php:12
 * @route '/admin/notifications'
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
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
export const latest = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: latest.url(options),
    method: 'get',
})

latest.definition = {
    methods: ["get","head"],
    url: '/admin/notifications/latest',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
latest.url = (options?: RouteQueryOptions) => {
    return latest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
latest.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: latest.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
latest.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: latest.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
    const latestForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: latest.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
        latestForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: latest.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\NotificationController::latest
 * @see app/Http/Controllers/Admin/NotificationController.php:25
 * @route '/admin/notifications/latest'
 */
        latestForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: latest.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    latest.form = latestForm
/**
* @see \App\Http\Controllers\Admin\NotificationController::readAll
 * @see app/Http/Controllers/Admin/NotificationController.php:52
 * @route '/admin/notifications/read-all'
 */
export const readAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

readAll.definition = {
    methods: ["post"],
    url: '/admin/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NotificationController::readAll
 * @see app/Http/Controllers/Admin/NotificationController.php:52
 * @route '/admin/notifications/read-all'
 */
readAll.url = (options?: RouteQueryOptions) => {
    return readAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NotificationController::readAll
 * @see app/Http/Controllers/Admin/NotificationController.php:52
 * @route '/admin/notifications/read-all'
 */
readAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\NotificationController::readAll
 * @see app/Http/Controllers/Admin/NotificationController.php:52
 * @route '/admin/notifications/read-all'
 */
    const readAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: readAll.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NotificationController::readAll
 * @see app/Http/Controllers/Admin/NotificationController.php:52
 * @route '/admin/notifications/read-all'
 */
        readAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: readAll.url(options),
            method: 'post',
        })
    
    readAll.form = readAllForm
/**
* @see \App\Http\Controllers\Admin\NotificationController::read
 * @see app/Http/Controllers/Admin/NotificationController.php:44
 * @route '/admin/notifications/{notification}/read'
 */
export const read = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: read.url(args, options),
    method: 'post',
})

read.definition = {
    methods: ["post"],
    url: '/admin/notifications/{notification}/read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\NotificationController::read
 * @see app/Http/Controllers/Admin/NotificationController.php:44
 * @route '/admin/notifications/{notification}/read'
 */
read.url = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notification: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    notification: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notification: args.notification,
                }

    return read.definition.url
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NotificationController::read
 * @see app/Http/Controllers/Admin/NotificationController.php:44
 * @route '/admin/notifications/{notification}/read'
 */
read.post = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: read.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\NotificationController::read
 * @see app/Http/Controllers/Admin/NotificationController.php:44
 * @route '/admin/notifications/{notification}/read'
 */
    const readForm = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: read.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NotificationController::read
 * @see app/Http/Controllers/Admin/NotificationController.php:44
 * @route '/admin/notifications/{notification}/read'
 */
        readForm.post = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: read.url(args, options),
            method: 'post',
        })
    
    read.form = readForm
/**
* @see \App\Http\Controllers\Admin\NotificationController::destroy
 * @see app/Http/Controllers/Admin/NotificationController.php:59
 * @route '/admin/notifications/{notification}'
 */
export const destroy = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/notifications/{notification}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\NotificationController::destroy
 * @see app/Http/Controllers/Admin/NotificationController.php:59
 * @route '/admin/notifications/{notification}'
 */
destroy.url = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { notification: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    notification: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        notification: args.notification,
                }

    return destroy.definition.url
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\NotificationController::destroy
 * @see app/Http/Controllers/Admin/NotificationController.php:59
 * @route '/admin/notifications/{notification}'
 */
destroy.delete = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\NotificationController::destroy
 * @see app/Http/Controllers/Admin/NotificationController.php:59
 * @route '/admin/notifications/{notification}'
 */
    const destroyForm = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\NotificationController::destroy
 * @see app/Http/Controllers/Admin/NotificationController.php:59
 * @route '/admin/notifications/{notification}'
 */
        destroyForm.delete = (args: { notification: string | number } | [notification: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const notifications = {
    index: Object.assign(index, index),
latest: Object.assign(latest, latest),
readAll: Object.assign(readAll, readAll),
read: Object.assign(read, read),
destroy: Object.assign(destroy, destroy),
}

export default notifications