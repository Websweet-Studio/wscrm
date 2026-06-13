import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/task-categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::index
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:15
 * @route '/admin/task-categories'
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
* @see \App\Http\Controllers\Admin\TaskCategoryController::store
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:26
 * @route '/admin/task-categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/task-categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::store
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:26
 * @route '/admin/task-categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::store
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:26
 * @route '/admin/task-categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::store
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:26
 * @route '/admin/task-categories'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::store
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:26
 * @route '/admin/task-categories'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
export const update = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/task-categories/{task_category}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
update.url = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { task_category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { task_category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    task_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        task_category: typeof args.task_category === 'object'
                ? args.task_category.id
                : args.task_category,
                }

    return update.definition.url
            .replace('{task_category}', parsedArgs.task_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
update.put = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
update.patch = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
    const updateForm = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
        updateForm.put = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::update
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:43
 * @route '/admin/task-categories/{task_category}'
 */
        updateForm.patch = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\TaskCategoryController::destroy
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:60
 * @route '/admin/task-categories/{task_category}'
 */
export const destroy = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/task-categories/{task_category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::destroy
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:60
 * @route '/admin/task-categories/{task_category}'
 */
destroy.url = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { task_category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { task_category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    task_category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        task_category: typeof args.task_category === 'object'
                ? args.task_category.id
                : args.task_category,
                }

    return destroy.definition.url
            .replace('{task_category}', parsedArgs.task_category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\TaskCategoryController::destroy
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:60
 * @route '/admin/task-categories/{task_category}'
 */
destroy.delete = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::destroy
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:60
 * @route '/admin/task-categories/{task_category}'
 */
    const destroyForm = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\TaskCategoryController::destroy
 * @see app/Http/Controllers/Admin/TaskCategoryController.php:60
 * @route '/admin/task-categories/{task_category}'
 */
        destroyForm.delete = (args: { task_category: number | { id: number } } | [task_category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const taskCategories = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default taskCategories