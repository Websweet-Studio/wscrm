import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:56
 * @route '/admin/websites/plugins'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/websites/plugins',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:56
 * @route '/admin/websites/plugins'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:56
 * @route '/admin/websites/plugins'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:56
 * @route '/admin/websites/plugins'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::store
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:56
 * @route '/admin/websites/plugins'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:85
 * @route '/admin/websites/plugins/{plugin}'
 */
export const update = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/admin/websites/plugins/{plugin}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:85
 * @route '/admin/websites/plugins/{plugin}'
 */
update.url = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plugin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plugin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plugin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plugin: typeof args.plugin === 'object'
                ? args.plugin.id
                : args.plugin,
                }

    return update.definition.url
            .replace('{plugin}', parsedArgs.plugin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:85
 * @route '/admin/websites/plugins/{plugin}'
 */
update.post = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:85
 * @route '/admin/websites/plugins/{plugin}'
 */
    const updateForm = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::update
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:85
 * @route '/admin/websites/plugins/{plugin}'
 */
        updateForm.post = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, options),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:122
 * @route '/admin/websites/plugins/{plugin}'
 */
export const destroy = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/websites/plugins/{plugin}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:122
 * @route '/admin/websites/plugins/{plugin}'
 */
destroy.url = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plugin: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { plugin: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    plugin: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plugin: typeof args.plugin === 'object'
                ? args.plugin.id
                : args.plugin,
                }

    return destroy.definition.url
            .replace('{plugin}', parsedArgs.plugin.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:122
 * @route '/admin/websites/plugins/{plugin}'
 */
destroy.delete = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:122
 * @route '/admin/websites/plugins/{plugin}'
 */
    const destroyForm = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::destroy
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:122
 * @route '/admin/websites/plugins/{plugin}'
 */
        destroyForm.delete = (args: { plugin: number | { id: number } } | [plugin: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:166
 * @route '/admin/websites/{website}/plugins'
 */
export const destroy = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/websites/{website}/plugins',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:166
 * @route '/admin/websites/{website}/plugins'
 */
destroy.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return destroy.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:166
 * @route '/admin/websites/{website}/plugins'
 */
destroy.delete = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:166
 * @route '/admin/websites/{website}/plugins'
 */
    const destroyForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:166
 * @route '/admin/websites/{website}/plugins'
 */
        destroyForm.delete = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
export const list = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(args, options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/admin/websites/{website}/plugins',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
list.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return list.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
list.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
list.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
    const listForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
        listForm.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::list
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:149
 * @route '/admin/websites/{website}/plugins'
 */
        listForm.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    list.form = listForm
const plugins = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
list: Object.assign(list, list),
}

export default plugins