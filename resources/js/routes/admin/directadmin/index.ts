import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/directadmin',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DirectAdminController::index
 * @see app/Http/Controllers/Admin/DirectAdminController.php:19
 * @route '/admin/directadmin'
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
* @see \App\Http\Controllers\Admin\DirectAdminController::settings
 * @see app/Http/Controllers/Admin/DirectAdminController.php:70
 * @route '/admin/directadmin/settings'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: settings.url(options),
    method: 'post',
})

settings.definition = {
    methods: ["post"],
    url: '/admin/directadmin/settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::settings
 * @see app/Http/Controllers/Admin/DirectAdminController.php:70
 * @route '/admin/directadmin/settings'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::settings
 * @see app/Http/Controllers/Admin/DirectAdminController.php:70
 * @route '/admin/directadmin/settings'
 */
settings.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: settings.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DirectAdminController::settings
 * @see app/Http/Controllers/Admin/DirectAdminController.php:70
 * @route '/admin/directadmin/settings'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: settings.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DirectAdminController::settings
 * @see app/Http/Controllers/Admin/DirectAdminController.php:70
 * @route '/admin/directadmin/settings'
 */
        settingsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: settings.url(options),
            method: 'post',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\Admin\DirectAdminController::suspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:101
 * @route '/admin/directadmin/accounts/{username}/suspend'
 */
export const suspend = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suspend.url(args, options),
    method: 'post',
})

suspend.definition = {
    methods: ["post"],
    url: '/admin/directadmin/accounts/{username}/suspend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::suspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:101
 * @route '/admin/directadmin/accounts/{username}/suspend'
 */
suspend.url = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { username: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    username: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        username: args.username,
                }

    return suspend.definition.url
            .replace('{username}', parsedArgs.username.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::suspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:101
 * @route '/admin/directadmin/accounts/{username}/suspend'
 */
suspend.post = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suspend.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DirectAdminController::suspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:101
 * @route '/admin/directadmin/accounts/{username}/suspend'
 */
    const suspendForm = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: suspend.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DirectAdminController::suspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:101
 * @route '/admin/directadmin/accounts/{username}/suspend'
 */
        suspendForm.post = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: suspend.url(args, options),
            method: 'post',
        })
    
    suspend.form = suspendForm
/**
* @see \App\Http\Controllers\Admin\DirectAdminController::unsuspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:118
 * @route '/admin/directadmin/accounts/{username}/unsuspend'
 */
export const unsuspend = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unsuspend.url(args, options),
    method: 'post',
})

unsuspend.definition = {
    methods: ["post"],
    url: '/admin/directadmin/accounts/{username}/unsuspend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::unsuspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:118
 * @route '/admin/directadmin/accounts/{username}/unsuspend'
 */
unsuspend.url = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { username: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    username: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        username: args.username,
                }

    return unsuspend.definition.url
            .replace('{username}', parsedArgs.username.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DirectAdminController::unsuspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:118
 * @route '/admin/directadmin/accounts/{username}/unsuspend'
 */
unsuspend.post = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: unsuspend.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DirectAdminController::unsuspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:118
 * @route '/admin/directadmin/accounts/{username}/unsuspend'
 */
    const unsuspendForm = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: unsuspend.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DirectAdminController::unsuspend
 * @see app/Http/Controllers/Admin/DirectAdminController.php:118
 * @route '/admin/directadmin/accounts/{username}/unsuspend'
 */
        unsuspendForm.post = (args: { username: string | number } | [username: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: unsuspend.url(args, options),
            method: 'post',
        })
    
    unsuspend.form = unsuspendForm
const directadmin = {
    index: Object.assign(index, index),
settings: Object.assign(settings, settings),
suspend: Object.assign(suspend, suspend),
unsuspend: Object.assign(unsuspend, unsuspend),
}

export default directadmin