import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/admin/system/update',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
update.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(options),
    method: 'get',
})
/**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
update.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(options),
    method: 'head',
})

    /**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: update.url(options),
        method: 'get',
    })

            /**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
        updateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: update.url(options),
            method: 'get',
        })
            /**
 * @see routes/web-update.php:15
 * @route '/admin/system/update'
 */
        updateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
export const checkUpdates = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkUpdates.url(options),
    method: 'get',
})

checkUpdates.definition = {
    methods: ["get","head"],
    url: '/admin/system/check-updates',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
checkUpdates.url = (options?: RouteQueryOptions) => {
    return checkUpdates.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
checkUpdates.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: checkUpdates.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
checkUpdates.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: checkUpdates.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
    const checkUpdatesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: checkUpdates.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
        checkUpdatesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkUpdates.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\UpdateController::checkUpdates
 * @see app/Http/Controllers/UpdateController.php:24
 * @route '/admin/system/check-updates'
 */
        checkUpdatesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: checkUpdates.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    checkUpdates.form = checkUpdatesForm
/**
* @see \App\Http\Controllers\UpdateController::performUpdate
 * @see app/Http/Controllers/UpdateController.php:60
 * @route '/admin/system/perform-update'
 */
export const performUpdate = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: performUpdate.url(options),
    method: 'post',
})

performUpdate.definition = {
    methods: ["post"],
    url: '/admin/system/perform-update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UpdateController::performUpdate
 * @see app/Http/Controllers/UpdateController.php:60
 * @route '/admin/system/perform-update'
 */
performUpdate.url = (options?: RouteQueryOptions) => {
    return performUpdate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UpdateController::performUpdate
 * @see app/Http/Controllers/UpdateController.php:60
 * @route '/admin/system/perform-update'
 */
performUpdate.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: performUpdate.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UpdateController::performUpdate
 * @see app/Http/Controllers/UpdateController.php:60
 * @route '/admin/system/perform-update'
 */
    const performUpdateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: performUpdate.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UpdateController::performUpdate
 * @see app/Http/Controllers/UpdateController.php:60
 * @route '/admin/system/perform-update'
 */
        performUpdateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: performUpdate.url(options),
            method: 'post',
        })
    
    performUpdate.form = performUpdateForm
/**
* @see \App\Http\Controllers\UpdateController::restoreBackup
 * @see app/Http/Controllers/UpdateController.php:115
 * @route '/admin/system/restore-backup'
 */
export const restoreBackup = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreBackup.url(options),
    method: 'post',
})

restoreBackup.definition = {
    methods: ["post"],
    url: '/admin/system/restore-backup',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UpdateController::restoreBackup
 * @see app/Http/Controllers/UpdateController.php:115
 * @route '/admin/system/restore-backup'
 */
restoreBackup.url = (options?: RouteQueryOptions) => {
    return restoreBackup.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UpdateController::restoreBackup
 * @see app/Http/Controllers/UpdateController.php:115
 * @route '/admin/system/restore-backup'
 */
restoreBackup.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreBackup.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\UpdateController::restoreBackup
 * @see app/Http/Controllers/UpdateController.php:115
 * @route '/admin/system/restore-backup'
 */
    const restoreBackupForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreBackup.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\UpdateController::restoreBackup
 * @see app/Http/Controllers/UpdateController.php:115
 * @route '/admin/system/restore-backup'
 */
        restoreBackupForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreBackup.url(options),
            method: 'post',
        })
    
    restoreBackup.form = restoreBackupForm
const system = {
    update: Object.assign(update, update),
checkUpdates: Object.assign(checkUpdates, checkUpdates),
performUpdate: Object.assign(performUpdate, performUpdate),
restoreBackup: Object.assign(restoreBackup, restoreBackup),
}

export default system