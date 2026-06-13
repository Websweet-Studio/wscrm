import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:221
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:221
 * @route '/admin/invoices/bulk/mark-paid'
 */
bulkMarkPaid.url = (options?: RouteQueryOptions) => {
    return bulkMarkPaid.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkMarkPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:221
 * @route '/admin/invoices/bulk/mark-paid'
 */
bulkMarkPaid.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: bulkMarkPaid.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:247
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:247
 * @route '/admin/invoices/bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::bulkDestroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:247
 * @route '/admin/invoices/bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:18
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:18
 * @route '/admin/invoices'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:18
 * @route '/admin/invoices'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::index
 * @see app/Http/Controllers/Admin/InvoiceController.php:18
 * @route '/admin/invoices'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:107
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:107
 * @route '/admin/invoices'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::store
 * @see app/Http/Controllers/Admin/InvoiceController.php:107
 * @route '/admin/invoices'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:139
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:139
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:139
 * @route '/admin/invoices/{invoice}'
 */
show.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::show
 * @see app/Http/Controllers/Admin/InvoiceController.php:139
 * @route '/admin/invoices/{invoice}'
 */
show.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:148
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:148
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:148
 * @route '/admin/invoices/{invoice}'
 */
update.put = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::update
 * @see app/Http/Controllers/Admin/InvoiceController.php:148
 * @route '/admin/invoices/{invoice}'
 */
update.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::destroy
 * @see app/Http/Controllers/Admin/InvoiceController.php:166
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:166
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:166
 * @route '/admin/invoices/{invoice}'
 */
destroy.delete = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:184
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:184
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:184
 * @route '/admin/invoices/{invoice}/download'
 */
download.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\InvoiceController::download
 * @see app/Http/Controllers/Admin/InvoiceController.php:184
 * @route '/admin/invoices/{invoice}/download'
 */
download.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::markPaid
 * @see app/Http/Controllers/Admin/InvoiceController.php:206
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:206
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:206
 * @route '/admin/invoices/{invoice}/mark-paid'
 */
markPaid.patch = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: markPaid.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:177
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
 * @see app/Http/Controllers/Admin/InvoiceController.php:177
 * @route '/admin/invoices/generate-renewals'
 */
generateRenewals.url = (options?: RouteQueryOptions) => {
    return generateRenewals.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\InvoiceController::generateRenewals
 * @see app/Http/Controllers/Admin/InvoiceController.php:177
 * @route '/admin/invoices/generate-renewals'
 */
generateRenewals.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateRenewals.url(options),
    method: 'post',
})
const invoices = {
    bulkMarkPaid: Object.assign(bulkMarkPaid, bulkMarkPaid),
bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
download: Object.assign(download, download),
markPaid: Object.assign(markPaid, markPaid),
generateRenewals: Object.assign(generateRenewals, generateRenewals),
}

export default invoices