import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/hosting-plans/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
    const bulkDestroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDestroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::bulkDestroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:102
 * @route '/admin/hosting-plans/bulk'
 */
        bulkDestroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDestroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    bulkDestroy.form = bulkDestroyForm
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::index
 * @see app/Http/Controllers/Admin/HostingPlanController.php:15
 * @route '/admin/hosting-plans'
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
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::create
 * @see app/Http/Controllers/Admin/HostingPlanController.php:31
 * @route '/admin/hosting-plans/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/hosting-plans',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::store
 * @see app/Http/Controllers/Admin/HostingPlanController.php:36
 * @route '/admin/hosting-plans'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const show = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.url = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return show.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.get = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
show.head = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
    const showForm = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
        showForm.get = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::show
 * @see app/Http/Controllers/Admin/HostingPlanController.php:58
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
        showForm.head = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
export const edit = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/hosting-plans/{hosting_plan}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.url = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return edit.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.get = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
edit.head = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
    const editForm = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
        editForm.get = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::edit
 * @see app/Http/Controllers/Admin/HostingPlanController.php:65
 * @route '/admin/hosting-plans/{hosting_plan}/edit'
 */
        editForm.head = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const update = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.url = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return update.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.put = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
update.patch = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
    const updateForm = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
        updateForm.put = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::update
 * @see app/Http/Controllers/Admin/HostingPlanController.php:72
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
        updateForm.patch = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
export const destroy = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/hosting-plans/{hosting_plan}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
destroy.url = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { hosting_plan: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { hosting_plan: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    hosting_plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        hosting_plan: typeof args.hosting_plan === 'object'
                ? args.hosting_plan.id
                : args.hosting_plan,
                }

    return destroy.definition.url
            .replace('{hosting_plan}', parsedArgs.hosting_plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
destroy.delete = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
    const destroyForm = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\HostingPlanController::destroy
 * @see app/Http/Controllers/Admin/HostingPlanController.php:94
 * @route '/admin/hosting-plans/{hosting_plan}'
 */
        destroyForm.delete = (args: { hosting_plan: string | number | { id: string | number } } | [hosting_plan: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const hostingPlans = {
    bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default hostingPlans