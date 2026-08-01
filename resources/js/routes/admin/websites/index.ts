import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/websites',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:21
 * @route '/admin/websites'
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
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:53
 * @route '/admin/websites'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/websites',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:53
 * @route '/admin/websites'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:53
 * @route '/admin/websites'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:53
 * @route '/admin/websites'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:53
 * @route '/admin/websites'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
export const show = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
show.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
show.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
show.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
    const showForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
        showForm.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:91
 * @route '/admin/websites/{website}'
 */
        showForm.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
export const update = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
update.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
update.put = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
update.patch = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
    const updateForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
        updateForm.put = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:61
 * @route '/admin/websites/{website}'
 */
        updateForm.patch = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:69
 * @route '/admin/websites/{website}'
 */
export const destroy = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:69
 * @route '/admin/websites/{website}'
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
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:69
 * @route '/admin/websites/{website}'
 */
destroy.delete = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:69
 * @route '/admin/websites/{website}'
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
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:69
 * @route '/admin/websites/{website}'
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
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:77
 * @route '/admin/websites/bulk'
 */
export const bulkDelete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDelete.url(options),
    method: 'delete',
})

bulkDelete.definition = {
    methods: ["delete"],
    url: '/admin/websites/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:77
 * @route '/admin/websites/bulk'
 */
bulkDelete.url = (options?: RouteQueryOptions) => {
    return bulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:77
 * @route '/admin/websites/bulk'
 */
bulkDelete.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDelete.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:77
 * @route '/admin/websites/bulk'
 */
    const bulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDelete.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:77
 * @route '/admin/websites/bulk'
 */
        bulkDeleteForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDelete.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    bulkDelete.form = bulkDeleteForm
const websites = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
bulkDelete: Object.assign(bulkDelete, bulkDelete),
}

export default websites