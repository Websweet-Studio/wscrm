import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/customer/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\AiController::index
 * @see app/Http/Controllers/Customer/AiController.php:25
 * @route '/customer/ai'
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
* @see \App\Http\Controllers\Customer\AiController::apiKey
 * @see app/Http/Controllers/Customer/AiController.php:192
 * @route '/customer/ai/api-key'
 */
export const apiKey = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apiKey.url(options),
    method: 'post',
})

apiKey.definition = {
    methods: ["post"],
    url: '/customer/ai/api-key',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\AiController::apiKey
 * @see app/Http/Controllers/Customer/AiController.php:192
 * @route '/customer/ai/api-key'
 */
apiKey.url = (options?: RouteQueryOptions) => {
    return apiKey.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::apiKey
 * @see app/Http/Controllers/Customer/AiController.php:192
 * @route '/customer/ai/api-key'
 */
apiKey.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: apiKey.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::apiKey
 * @see app/Http/Controllers/Customer/AiController.php:192
 * @route '/customer/ai/api-key'
 */
    const apiKeyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: apiKey.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::apiKey
 * @see app/Http/Controllers/Customer/AiController.php:192
 * @route '/customer/ai/api-key'
 */
        apiKeyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: apiKey.url(options),
            method: 'post',
        })
    
    apiKey.form = apiKeyForm
/**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/customer/ai/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
    const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: history.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
        historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\AiController::history
 * @see app/Http/Controllers/Customer/AiController.php:129
 * @route '/customer/ai/history'
 */
        historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: history.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    history.form = historyForm
/**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/customer/ai/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\AiController::exportMethod
 * @see app/Http/Controllers/Customer/AiController.php:152
 * @route '/customer/ai/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
export const packages = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: packages.url(options),
    method: 'get',
})

packages.definition = {
    methods: ["get","head"],
    url: '/customer/ai/packages',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
packages.url = (options?: RouteQueryOptions) => {
    return packages.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
packages.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: packages.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
packages.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: packages.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
    const packagesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: packages.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
        packagesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: packages.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\AiController::packages
 * @see app/Http/Controllers/Customer/AiController.php:208
 * @route '/customer/ai/packages'
 */
        packagesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: packages.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    packages.form = packagesForm
/**
* @see \App\Http\Controllers\Customer\AiController::buy
 * @see app/Http/Controllers/Customer/AiController.php:226
 * @route '/customer/ai/packages/{package}/buy'
 */
export const buy = (args: { package: number | { id: number } } | [packageParam: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: buy.url(args, options),
    method: 'post',
})

buy.definition = {
    methods: ["post"],
    url: '/customer/ai/packages/{package}/buy',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\AiController::buy
 * @see app/Http/Controllers/Customer/AiController.php:226
 * @route '/customer/ai/packages/{package}/buy'
 */
buy.url = (args: { package: number | { id: number } } | [packageParam: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { package: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { package: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    package: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        package: typeof args.package === 'object'
                ? args.package.id
                : args.package,
                }

    return buy.definition.url
            .replace('{package}', parsedArgs.package.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::buy
 * @see app/Http/Controllers/Customer/AiController.php:226
 * @route '/customer/ai/packages/{package}/buy'
 */
buy.post = (args: { package: number | { id: number } } | [packageParam: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: buy.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::buy
 * @see app/Http/Controllers/Customer/AiController.php:226
 * @route '/customer/ai/packages/{package}/buy'
 */
    const buyForm = (args: { package: number | { id: number } } | [packageParam: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: buy.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::buy
 * @see app/Http/Controllers/Customer/AiController.php:226
 * @route '/customer/ai/packages/{package}/buy'
 */
        buyForm.post = (args: { package: number | { id: number } } | [packageParam: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: buy.url(args, options),
            method: 'post',
        })
    
    buy.form = buyForm
const ai = {
    index: Object.assign(index, index),
apiKey: Object.assign(apiKey, apiKey),
history: Object.assign(history, history),
export: Object.assign(exportMethod, exportMethod),
packages: Object.assign(packages, packages),
buy: Object.assign(buy, buy),
}

export default ai