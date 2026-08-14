import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/ai/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\Ai\SettingController::index
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:16
 * @route '/admin/ai/settings'
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
* @see \App\Http\Controllers\Admin\Ai\SettingController::save
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:33
 * @route '/admin/ai/settings'
 */
export const save = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: save.url(options),
    method: 'patch',
})

save.definition = {
    methods: ["patch"],
    url: '/admin/ai/settings',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::save
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:33
 * @route '/admin/ai/settings'
 */
save.url = (options?: RouteQueryOptions) => {
    return save.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\Ai\SettingController::save
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:33
 * @route '/admin/ai/settings'
 */
save.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: save.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\Ai\SettingController::save
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:33
 * @route '/admin/ai/settings'
 */
    const saveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: save.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\Ai\SettingController::save
 * @see app/Http/Controllers/Admin/Ai/SettingController.php:33
 * @route '/admin/ai/settings'
 */
        saveForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: save.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    save.form = saveForm
const settings = {
    index: Object.assign(index, index),
save: Object.assign(save, save),
}

export default settings