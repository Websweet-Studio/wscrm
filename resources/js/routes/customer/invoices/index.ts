import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/customer/invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\InvoiceController::index
 * @see app/Http/Controllers/Customer/InvoiceController.php:19
 * @route '/customer/invoices'
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
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
 */
export const show = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/customer/invoices/{invoice}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
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
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
 */
show.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
 */
show.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
 */
    const showForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
 */
        showForm.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\InvoiceController::show
 * @see app/Http/Controllers/Customer/InvoiceController.php:36
 * @route '/customer/invoices/{invoice}'
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
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
export const payment = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payment.url(args, options),
    method: 'get',
})

payment.definition = {
    methods: ["get","head"],
    url: '/customer/invoices/{invoice}/payment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
payment.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return payment.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
payment.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payment.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
payment.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: payment.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
    const paymentForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: payment.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
        paymentForm.get = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payment.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\InvoiceController::payment
 * @see app/Http/Controllers/Customer/InvoiceController.php:55
 * @route '/customer/invoices/{invoice}/payment'
 */
        paymentForm.head = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payment.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    payment.form = paymentForm
/**
* @see \App\Http\Controllers\Customer\InvoiceController::processPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:79
 * @route '/customer/invoices/{invoice}/process-payment'
 */
export const processPayment = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processPayment.url(args, options),
    method: 'post',
})

processPayment.definition = {
    methods: ["post"],
    url: '/customer/invoices/{invoice}/process-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\InvoiceController::processPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:79
 * @route '/customer/invoices/{invoice}/process-payment'
 */
processPayment.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return processPayment.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\InvoiceController::processPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:79
 * @route '/customer/invoices/{invoice}/process-payment'
 */
processPayment.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: processPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\InvoiceController::processPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:79
 * @route '/customer/invoices/{invoice}/process-payment'
 */
    const processPaymentForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: processPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\InvoiceController::processPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:79
 * @route '/customer/invoices/{invoice}/process-payment'
 */
        processPaymentForm.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: processPayment.url(args, options),
            method: 'post',
        })
    
    processPayment.form = processPaymentForm
/**
* @see \App\Http\Controllers\Customer\InvoiceController::confirmPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:114
 * @route '/customer/invoices/{invoice}/confirm-payment'
 */
export const confirmPayment = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmPayment.url(args, options),
    method: 'post',
})

confirmPayment.definition = {
    methods: ["post"],
    url: '/customer/invoices/{invoice}/confirm-payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\InvoiceController::confirmPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:114
 * @route '/customer/invoices/{invoice}/confirm-payment'
 */
confirmPayment.url = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return confirmPayment.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\InvoiceController::confirmPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:114
 * @route '/customer/invoices/{invoice}/confirm-payment'
 */
confirmPayment.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmPayment.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\InvoiceController::confirmPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:114
 * @route '/customer/invoices/{invoice}/confirm-payment'
 */
    const confirmPaymentForm = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirmPayment.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\InvoiceController::confirmPayment
 * @see app/Http/Controllers/Customer/InvoiceController.php:114
 * @route '/customer/invoices/{invoice}/confirm-payment'
 */
        confirmPaymentForm.post = (args: { invoice: number | { id: number } } | [invoice: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirmPayment.url(args, options),
            method: 'post',
        })
    
    confirmPayment.form = confirmPaymentForm
const invoices = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
payment: Object.assign(payment, payment),
processPayment: Object.assign(processPayment, processPayment),
confirmPayment: Object.assign(confirmPayment, confirmPayment),
}

export default invoices