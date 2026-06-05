import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/banks',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BankController::index
 * @see app/Http/Controllers/Admin/BankController.php:17
 * @route '/admin/banks'
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
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/banks/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BankController::create
 * @see app/Http/Controllers/Admin/BankController.php:32
 * @route '/admin/banks/create'
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
* @see \App\Http\Controllers\Admin\BankController::store
 * @see app/Http/Controllers/Admin/BankController.php:40
 * @route '/admin/banks'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/banks',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BankController::store
 * @see app/Http/Controllers/Admin/BankController.php:40
 * @route '/admin/banks'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::store
 * @see app/Http/Controllers/Admin/BankController.php:40
 * @route '/admin/banks'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::store
 * @see app/Http/Controllers/Admin/BankController.php:40
 * @route '/admin/banks'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::store
 * @see app/Http/Controllers/Admin/BankController.php:40
 * @route '/admin/banks'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
export const show = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/banks/{bank}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
show.url = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bank: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bank: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bank: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bank: typeof args.bank === 'object'
                ? args.bank.id
                : args.bank,
                }

    return show.definition.url
            .replace('{bank}', parsedArgs.bank.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
show.get = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
show.head = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
    const showForm = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
        showForm.get = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BankController::show
 * @see app/Http/Controllers/Admin/BankController.php:64
 * @route '/admin/banks/{bank}'
 */
        showForm.head = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
export const edit = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/banks/{bank}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
edit.url = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bank: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bank: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bank: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bank: typeof args.bank === 'object'
                ? args.bank.id
                : args.bank,
                }

    return edit.definition.url
            .replace('{bank}', parsedArgs.bank.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
edit.get = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
edit.head = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
    const editForm = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
        editForm.get = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BankController::edit
 * @see app/Http/Controllers/Admin/BankController.php:78
 * @route '/admin/banks/{bank}/edit'
 */
        editForm.head = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
export const update = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/banks/{bank}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
update.url = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bank: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bank: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bank: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bank: typeof args.bank === 'object'
                ? args.bank.id
                : args.bank,
                }

    return update.definition.url
            .replace('{bank}', parsedArgs.bank.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
update.put = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
update.patch = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
    const updateForm = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
        updateForm.put = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\BankController::update
 * @see app/Http/Controllers/Admin/BankController.php:88
 * @route '/admin/banks/{bank}'
 */
        updateForm.patch = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\BankController::destroy
 * @see app/Http/Controllers/Admin/BankController.php:112
 * @route '/admin/banks/{bank}'
 */
export const destroy = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/banks/{bank}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\BankController::destroy
 * @see app/Http/Controllers/Admin/BankController.php:112
 * @route '/admin/banks/{bank}'
 */
destroy.url = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bank: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bank: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bank: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bank: typeof args.bank === 'object'
                ? args.bank.id
                : args.bank,
                }

    return destroy.definition.url
            .replace('{bank}', parsedArgs.bank.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::destroy
 * @see app/Http/Controllers/Admin/BankController.php:112
 * @route '/admin/banks/{bank}'
 */
destroy.delete = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::destroy
 * @see app/Http/Controllers/Admin/BankController.php:112
 * @route '/admin/banks/{bank}'
 */
    const destroyForm = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::destroy
 * @see app/Http/Controllers/Admin/BankController.php:112
 * @route '/admin/banks/{bank}'
 */
        destroyForm.delete = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\BankController::toggleStatus
 * @see app/Http/Controllers/Admin/BankController.php:129
 * @route '/admin/banks/{bank}/toggle-status'
 */
export const toggleStatus = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

toggleStatus.definition = {
    methods: ["patch"],
    url: '/admin/banks/{bank}/toggle-status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\BankController::toggleStatus
 * @see app/Http/Controllers/Admin/BankController.php:129
 * @route '/admin/banks/{bank}/toggle-status'
 */
toggleStatus.url = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { bank: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { bank: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    bank: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        bank: typeof args.bank === 'object'
                ? args.bank.id
                : args.bank,
                }

    return toggleStatus.definition.url
            .replace('{bank}', parsedArgs.bank.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BankController::toggleStatus
 * @see app/Http/Controllers/Admin/BankController.php:129
 * @route '/admin/banks/{bank}/toggle-status'
 */
toggleStatus.patch = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleStatus.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\BankController::toggleStatus
 * @see app/Http/Controllers/Admin/BankController.php:129
 * @route '/admin/banks/{bank}/toggle-status'
 */
    const toggleStatusForm = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BankController::toggleStatus
 * @see app/Http/Controllers/Admin/BankController.php:129
 * @route '/admin/banks/{bank}/toggle-status'
 */
        toggleStatusForm.patch = (args: { bank: number | { id: number } } | [bank: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleStatus.form = toggleStatusForm
const banks = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggleStatus: Object.assign(toggleStatus, toggleStatus),
}

export default banks