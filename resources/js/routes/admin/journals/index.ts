import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/journals',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::index
 * @see app/Http/Controllers/Admin/JournalEntryController.php:22
 * @route '/admin/journals'
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
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/journals/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::create
 * @see app/Http/Controllers/Admin/JournalEntryController.php:45
 * @route '/admin/journals/create'
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
* @see \App\Http\Controllers\Admin\JournalEntryController::store
 * @see app/Http/Controllers/Admin/JournalEntryController.php:69
 * @route '/admin/journals'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/journals',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::store
 * @see app/Http/Controllers/Admin/JournalEntryController.php:69
 * @route '/admin/journals'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::store
 * @see app/Http/Controllers/Admin/JournalEntryController.php:69
 * @route '/admin/journals'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::store
 * @see app/Http/Controllers/Admin/JournalEntryController.php:69
 * @route '/admin/journals'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::store
 * @see app/Http/Controllers/Admin/JournalEntryController.php:69
 * @route '/admin/journals'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
export const edit = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/journals/{journal}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
edit.url = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { journal: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { journal: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    journal: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        journal: typeof args.journal === 'object'
                ? args.journal.id
                : args.journal,
                }

    return edit.definition.url
            .replace('{journal}', parsedArgs.journal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
edit.get = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
edit.head = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
    const editForm = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
        editForm.get = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::edit
 * @see app/Http/Controllers/Admin/JournalEntryController.php:57
 * @route '/admin/journals/{journal}/edit'
 */
        editForm.head = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
export const update = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/journals/{journal}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
update.url = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { journal: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { journal: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    journal: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        journal: typeof args.journal === 'object'
                ? args.journal.id
                : args.journal,
                }

    return update.definition.url
            .replace('{journal}', parsedArgs.journal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
update.put = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
update.patch = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
    const updateForm = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
        updateForm.put = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::update
 * @see app/Http/Controllers/Admin/JournalEntryController.php:81
 * @route '/admin/journals/{journal}'
 */
        updateForm.patch = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\JournalEntryController::destroy
 * @see app/Http/Controllers/Admin/JournalEntryController.php:89
 * @route '/admin/journals/{journal}'
 */
export const destroy = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/journals/{journal}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::destroy
 * @see app/Http/Controllers/Admin/JournalEntryController.php:89
 * @route '/admin/journals/{journal}'
 */
destroy.url = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { journal: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { journal: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    journal: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        journal: typeof args.journal === 'object'
                ? args.journal.id
                : args.journal,
                }

    return destroy.definition.url
            .replace('{journal}', parsedArgs.journal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::destroy
 * @see app/Http/Controllers/Admin/JournalEntryController.php:89
 * @route '/admin/journals/{journal}'
 */
destroy.delete = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::destroy
 * @see app/Http/Controllers/Admin/JournalEntryController.php:89
 * @route '/admin/journals/{journal}'
 */
    const destroyForm = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::destroy
 * @see app/Http/Controllers/Admin/JournalEntryController.php:89
 * @route '/admin/journals/{journal}'
 */
        destroyForm.delete = (args: { journal: number | { id: number } } | [journal: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
export const report = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})

report.definition = {
    methods: ["get","head"],
    url: '/admin/journals/report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
report.url = (options?: RouteQueryOptions) => {
    return report.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
report.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: report.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
report.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: report.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
    const reportForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: report.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
        reportForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::report
 * @see app/Http/Controllers/Admin/JournalEntryController.php:97
 * @route '/admin/journals/report'
 */
        reportForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: report.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    report.form = reportForm
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/journals/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportMethod
 * @see app/Http/Controllers/Admin/JournalEntryController.php:121
 * @route '/admin/journals/export'
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
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
export const exportExcel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportExcel.url(options),
    method: 'get',
})

exportExcel.definition = {
    methods: ["get","head"],
    url: '/admin/journals/export-excel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
exportExcel.url = (options?: RouteQueryOptions) => {
    return exportExcel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
exportExcel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportExcel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
exportExcel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportExcel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
    const exportExcelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportExcel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
        exportExcelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportExcel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\JournalEntryController::exportExcel
 * @see app/Http/Controllers/Admin/JournalEntryController.php:163
 * @route '/admin/journals/export-excel'
 */
        exportExcelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportExcel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportExcel.form = exportExcelForm
const journals = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
report: Object.assign(report, report),
export: Object.assign(exportMethod, exportMethod),
exportExcel: Object.assign(exportExcel, exportExcel),
}

export default journals