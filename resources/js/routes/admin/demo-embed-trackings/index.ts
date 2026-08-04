import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/demo-embed-trackings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::index
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:13
 * @route '/admin/demo-embed-trackings'
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
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::destroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:55
 * @route '/admin/demo-embed-trackings/{demo_embed_tracking}'
 */
export const destroy = (args: { demo_embed_tracking: string | number | { id: string | number } } | [demo_embed_tracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/demo-embed-trackings/{demo_embed_tracking}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::destroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:55
 * @route '/admin/demo-embed-trackings/{demo_embed_tracking}'
 */
destroy.url = (args: { demo_embed_tracking: string | number | { id: string | number } } | [demo_embed_tracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demo_embed_tracking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demo_embed_tracking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demo_embed_tracking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demo_embed_tracking: typeof args.demo_embed_tracking === 'object'
                ? args.demo_embed_tracking.id
                : args.demo_embed_tracking,
                }

    return destroy.definition.url
            .replace('{demo_embed_tracking}', parsedArgs.demo_embed_tracking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::destroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:55
 * @route '/admin/demo-embed-trackings/{demo_embed_tracking}'
 */
destroy.delete = (args: { demo_embed_tracking: string | number | { id: string | number } } | [demo_embed_tracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::destroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:55
 * @route '/admin/demo-embed-trackings/{demo_embed_tracking}'
 */
    const destroyForm = (args: { demo_embed_tracking: string | number | { id: string | number } } | [demo_embed_tracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::destroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:55
 * @route '/admin/demo-embed-trackings/{demo_embed_tracking}'
 */
        destroyForm.delete = (args: { demo_embed_tracking: string | number | { id: string | number } } | [demo_embed_tracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::toggleBlock
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:43
 * @route '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block'
 */
export const toggleBlock = (args: { demoEmbedTracking: string | number | { id: string | number } } | [demoEmbedTracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleBlock.url(args, options),
    method: 'patch',
})

toggleBlock.definition = {
    methods: ["patch"],
    url: '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::toggleBlock
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:43
 * @route '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block'
 */
toggleBlock.url = (args: { demoEmbedTracking: string | number | { id: string | number } } | [demoEmbedTracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { demoEmbedTracking: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { demoEmbedTracking: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    demoEmbedTracking: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        demoEmbedTracking: typeof args.demoEmbedTracking === 'object'
                ? args.demoEmbedTracking.id
                : args.demoEmbedTracking,
                }

    return toggleBlock.definition.url
            .replace('{demoEmbedTracking}', parsedArgs.demoEmbedTracking.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::toggleBlock
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:43
 * @route '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block'
 */
toggleBlock.patch = (args: { demoEmbedTracking: string | number | { id: string | number } } | [demoEmbedTracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleBlock.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::toggleBlock
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:43
 * @route '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block'
 */
    const toggleBlockForm = (args: { demoEmbedTracking: string | number | { id: string | number } } | [demoEmbedTracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleBlock.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::toggleBlock
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:43
 * @route '/admin/demo-embed-trackings/{demoEmbedTracking}/toggle-block'
 */
        toggleBlockForm.patch = (args: { demoEmbedTracking: string | number | { id: string | number } } | [demoEmbedTracking: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleBlock.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleBlock.form = toggleBlockForm
/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::bulkDestroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:62
 * @route '/admin/demo-embed-trackings-bulk'
 */
export const bulkDestroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

bulkDestroy.definition = {
    methods: ["delete"],
    url: '/admin/demo-embed-trackings-bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::bulkDestroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:62
 * @route '/admin/demo-embed-trackings-bulk'
 */
bulkDestroy.url = (options?: RouteQueryOptions) => {
    return bulkDestroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::bulkDestroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:62
 * @route '/admin/demo-embed-trackings-bulk'
 */
bulkDestroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDestroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::bulkDestroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:62
 * @route '/admin/demo-embed-trackings-bulk'
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
* @see \App\Http\Controllers\Admin\DemoEmbedTrackingController::bulkDestroy
 * @see app/Http/Controllers/Admin/DemoEmbedTrackingController.php:62
 * @route '/admin/demo-embed-trackings-bulk'
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
const demoEmbedTrackings = {
    index: Object.assign(index, index),
destroy: Object.assign(destroy, destroy),
toggleBlock: Object.assign(toggleBlock, toggleBlock),
bulkDestroy: Object.assign(bulkDestroy, bulkDestroy),
}

export default demoEmbedTrackings