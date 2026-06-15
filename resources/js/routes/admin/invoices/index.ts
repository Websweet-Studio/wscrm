import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:271
 * @route '/admin/invoices/bulk/mark-paid'
 */
export const bulkMarkPaid = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: bulkMarkPaid.url(options),
    method: 'patch',
})

bulkMarkPaid.definition = {
    methods: ["patch"],
    url: '/admin/invoices/bulk/mark-paid',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:271
 * @route '/admin/invoices/bulk/mark-paid'
 */
bulkMarkPaid.url = (options?: RouteQueryOptions) => {
    return bulkMarkPaid.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:271
 * @route '/admin/invoices/bulk/mark-paid'
 */
bulkMarkPaid.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: bulkMarkPaid.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:271
 * @route '/admin/invoices/bulk/mark-paid'
 */
    const bulkMarkPaidForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkMarkPaid.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:271
 * @route '/admin/invoices/bulk/mark-paid'
 */
        bulkMarkPaidForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkMarkPaid.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    bulkMarkPaid.form = bulkMarkPaidForm
/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:297
 * @route '/admin/invoices/bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/invoices/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:297
 * @route '/admin/invoices/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:297
 * @route '/admin/invoices/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:297
 * @route '/admin/invoices/bulk'
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
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:297
 * @route '/admin/invoices/bulk'
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
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:19
 * @route '/admin/invoices'
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
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:108
 * @route '/admin/invoices'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/invoices',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:108
 * @route '/admin/invoices'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:108
 * @route '/admin/invoices'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:108
 * @route '/admin/invoices'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:108
 * @route '/admin/invoices'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
export const show = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/invoices/{invoice}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
show.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return show.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
show.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
show.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
    const showForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
        showForm.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:140
 * @route '/admin/invoices/{invoice}'
 */
        showForm.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
export const update = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/invoices/{invoice}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
update.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return update.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
update.put = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
update.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
    const updateForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
        updateForm.put = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:149
 * @route '/admin/invoices/{invoice}'
 */
        updateForm.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:167
 * @route '/admin/invoices/{invoice}'
 */
export const destroy = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/invoices/{invoice}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:167
 * @route '/admin/invoices/{invoice}'
 */
destroy.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return destroy.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:167
 * @route '/admin/invoices/{invoice}'
 */
destroy.delete = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:167
 * @route '/admin/invoices/{invoice}'
 */
    const destroyForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:167
 * @route '/admin/invoices/{invoice}'
 */
        destroyForm.delete = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
export const download = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/admin/invoices/{invoice}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
download.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return download.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
download.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
download.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
    const downloadForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: download.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
        downloadForm.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: download.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:185
 * @route '/admin/invoices/{invoice}/download'
 */
        downloadForm.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: download.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    download.form = downloadForm
/**
* @see \App\Http\Controllers\Admin\InvoiceController::send
 * @see app/Http/Controllers/Admin/InvoiceController.php:207
 * @route '/admin/invoices/{invoice}/send'
 */
export const send = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(args, options),
    method: 'post',
})

send.definition = {
    methods: ["post"],
    url: '/admin/invoices/{invoice}/send',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::send
 * @see app/Http/Controllers/Admin/InvoiceController.php:207
 * @route '/admin/invoices/{invoice}/send'
 */
send.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return send.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::send
 * @see app/Http/Controllers/Admin/InvoiceController.php:207
 * @route '/admin/invoices/{invoice}/send'
 */
send.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::send
 * @see app/Http/Controllers/Admin/InvoiceController.php:207
 * @route '/admin/invoices/{invoice}/send'
 */
    const sendForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: send.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::send
 * @see app/Http/Controllers/Admin/InvoiceController.php:207
 * @route '/admin/invoices/{invoice}/send'
 */
        sendForm.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: send.url(args, options),
            method: 'post',
        })
    
    send.form = sendForm
/**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:256
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
export const markPaid = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: markPaid.url(args, options),
    method: 'patch',
})

markPaid.definition = {
    methods: ["patch"],
    url: '/admin/invoices/{invoice}/mark-paid',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:256
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
markPaid.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { invoice: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: typeof args.invoice === 'object'
                ? args.invoice.id
                : args.invoice,
                }

    return markPaid.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:256
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
markPaid.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: markPaid.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:256
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
    const markPaidForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markPaid.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:256
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
        markPaidForm.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markPaid.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    markPaid.form = markPaidForm
/**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:178
 * @route '/admin/invoices/generate-renewals'
 */
export const generateRenewals = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateRenewals.url(options),
    method: 'post',
})

generateRenewals.definition = {
    methods: ["post"],
    url: '/admin/invoices/generate-renewals',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:178
 * @route '/admin/invoices/generate-renewals'
 */
generateRenewals.url = (options?: RouteQueryOptions) => {
    return generateRenewals.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:178
 * @route '/admin/invoices/generate-renewals'
 */
generateRenewals.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateRenewals.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:178
 * @route '/admin/invoices/generate-renewals'
 */
    const generateRenewalsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: generateRenewals.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:178
 * @route '/admin/invoices/generate-renewals'
 */
        generateRenewalsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: generateRenewals.url(options),
            method: 'post',
        })
    
    generateRenewals.form = generateRenewalsForm
const invoices = {
    bulkMarkPaid: Object.assign(bulkMarkPaid, bulkMarkPaid),
bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
download: Object.assign(download, download),
send: Object.assign(send, send),
markPaid: Object.assign(markPaid, markPaid),
generateRenewals: Object.assign(generateRenewals, generateRenewals),
}

export default invoices