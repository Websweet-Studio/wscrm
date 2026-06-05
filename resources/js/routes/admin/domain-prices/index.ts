import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/domain-prices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::index
 * @see app/Http/Controllers/Admin/DomainPriceController.php:14
 * @route '/admin/domain-prices'
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
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/domain-prices/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::create
 * @see app/Http/Controllers/Admin/DomainPriceController.php:47
 * @route '/admin/domain-prices/create'
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
* @see \App\Http\Controllers\Admin\DomainPriceController::store
 * @see app/Http/Controllers/Admin/DomainPriceController.php:52
 * @route '/admin/domain-prices'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/domain-prices',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::store
 * @see app/Http/Controllers/Admin/DomainPriceController.php:52
 * @route '/admin/domain-prices'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::store
 * @see app/Http/Controllers/Admin/DomainPriceController.php:52
 * @route '/admin/domain-prices'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::store
 * @see app/Http/Controllers/Admin/DomainPriceController.php:52
 * @route '/admin/domain-prices'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::store
 * @see app/Http/Controllers/Admin/DomainPriceController.php:52
 * @route '/admin/domain-prices'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
export const show = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/domain-prices/{domain_price}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
show.url = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { domain_price: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { domain_price: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    domain_price: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        domain_price: typeof args.domain_price === 'object'
                ? args.domain_price.id
                : args.domain_price,
                }

    return show.definition.url
            .replace('{domain_price}', parsedArgs.domain_price.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
show.get = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
show.head = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
    const showForm = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
        showForm.get = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::show
 * @see app/Http/Controllers/Admin/DomainPriceController.php:69
 * @route '/admin/domain-prices/{domain_price}'
 */
        showForm.head = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
export const edit = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/domain-prices/{domain_price}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
edit.url = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { domain_price: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { domain_price: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    domain_price: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        domain_price: typeof args.domain_price === 'object'
                ? args.domain_price.id
                : args.domain_price,
                }

    return edit.definition.url
            .replace('{domain_price}', parsedArgs.domain_price.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
edit.get = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
edit.head = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
    const editForm = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
        editForm.get = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::edit
 * @see app/Http/Controllers/Admin/DomainPriceController.php:76
 * @route '/admin/domain-prices/{domain_price}/edit'
 */
        editForm.head = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
export const update = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/domain-prices/{domain_price}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
update.url = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { domain_price: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { domain_price: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    domain_price: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        domain_price: typeof args.domain_price === 'object'
                ? args.domain_price.id
                : args.domain_price,
                }

    return update.definition.url
            .replace('{domain_price}', parsedArgs.domain_price.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
update.put = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
update.patch = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
    const updateForm = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
        updateForm.put = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::update
 * @see app/Http/Controllers/Admin/DomainPriceController.php:83
 * @route '/admin/domain-prices/{domain_price}'
 */
        updateForm.patch = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\DomainPriceController::destroy
 * @see app/Http/Controllers/Admin/DomainPriceController.php:100
 * @route '/admin/domain-prices/{domain_price}'
 */
export const destroy = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/domain-prices/{domain_price}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::destroy
 * @see app/Http/Controllers/Admin/DomainPriceController.php:100
 * @route '/admin/domain-prices/{domain_price}'
 */
destroy.url = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { domain_price: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { domain_price: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    domain_price: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        domain_price: typeof args.domain_price === 'object'
                ? args.domain_price.id
                : args.domain_price,
                }

    return destroy.definition.url
            .replace('{domain_price}', parsedArgs.domain_price.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DomainPriceController::destroy
 * @see app/Http/Controllers/Admin/DomainPriceController.php:100
 * @route '/admin/domain-prices/{domain_price}'
 */
destroy.delete = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DomainPriceController::destroy
 * @see app/Http/Controllers/Admin/DomainPriceController.php:100
 * @route '/admin/domain-prices/{domain_price}'
 */
    const destroyForm = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DomainPriceController::destroy
 * @see app/Http/Controllers/Admin/DomainPriceController.php:100
 * @route '/admin/domain-prices/{domain_price}'
 */
        destroyForm.delete = (args: { domain_price: number | { id: number } } | [domain_price: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const domainPrices = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default domainPrices