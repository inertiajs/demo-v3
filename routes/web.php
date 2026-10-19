<?php

use App\Http\Controllers\Crm;
use App\Http\Controllers\Feature;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Crm\DashboardController::class)->name('dashboard');

    // CRM Routes
    Route::resource('contacts', Crm\ContactController::class);
    Route::post('contacts/{contact}/favorite', [Crm\ContactController::class, 'favorite'])->name('contacts.favorite');
    Route::resource('organizations', Crm\OrganizationController::class)->only(['index', 'show', 'update']);
    Route::resource('contacts.notes', Crm\NoteController::class)->only(['store']);

    // Feature Showcase Routes
    Route::prefix('features')->name('features.')->group(function () {
        // Forms
        Route::prefix('forms')->name('forms.')->group(function () {
            Route::get('use-form', [Feature\FormController::class, 'useForm'])->name('use-form');
            Route::post('use-form', [Feature\FormController::class, 'submitUseForm']);

            Route::get('form-component', [Feature\FormController::class, 'formComponent'])->name('form-component');
            Route::post('form-component', [Feature\FormController::class, 'submitFormComponent']);

            Route::get('file-uploads', [Feature\FormController::class, 'fileUploads'])->name('file-uploads');
            Route::post('file-uploads', [Feature\FormController::class, 'submitFileUploads']);

            Route::get('validation', [Feature\FormController::class, 'validation'])->name('validation');
            Route::post('validation', [Feature\FormController::class, 'submitValidation']);
            Route::post('validation/secondary', [Feature\FormController::class, 'submitValidationSecondary'])->name('validation.secondary');

            Route::get('precognition', [Feature\FormController::class, 'precognition'])->name('precognition');
            Route::post('precognition', [Feature\FormController::class, 'storeAccount'])->middleware('precognitive');

            Route::get('optimistic-updates', [Feature\FormController::class, 'optimisticUpdates'])->name('optimistic-updates');
            Route::post('optimistic-toggle/{contact}', [Feature\FormController::class, 'toggleFavorite'])->name('optimistic-toggle');

            Route::get('use-form-context', [Feature\FormController::class, 'useFormContext'])->name('use-form-context');

            Route::get('dotted-keys', [Feature\FormController::class, 'dottedKeys'])->name('dotted-keys');
            Route::post('dotted-keys', [Feature\FormController::class, 'submitDottedKeys']);

            Route::get('wayfinder', [Feature\FormController::class, 'wayfinder'])->name('wayfinder');
        });

        // Navigation
        Route::prefix('navigation')->name('navigation.')->group(function () {
            Route::get('links', [Feature\NavigationController::class, 'links'])->name('links');
            Route::match(['post', 'put', 'patch', 'delete'], 'links', [Feature\NavigationController::class, 'linksAction']);

            Route::get('preserve-state', [Feature\NavigationController::class, 'preserveState'])->name('preserve-state');
            Route::get('preserve-scroll', [Feature\NavigationController::class, 'preserveScroll'])->name('preserve-scroll');
            Route::get('view-transitions', [Feature\NavigationController::class, 'viewTransitions'])->name('view-transitions');

            Route::get('history-management', [Feature\NavigationController::class, 'historyManagement'])->name('history-management');
            Route::post('history-management', [Feature\NavigationController::class, 'historyAction']);

            Route::get('async-requests', [Feature\NavigationController::class, 'asyncRequests'])->name('async-requests');
            Route::get('async-slow', [Feature\NavigationController::class, 'asyncSlow'])->name('async-slow');

            Route::get('manual-visits', [Feature\NavigationController::class, 'manualVisits'])->name('manual-visits');

            Route::get('redirects', [Feature\NavigationController::class, 'redirectDemo'])->name('redirects');
            Route::post('redirects/back', [Feature\NavigationController::class, 'redirectStandard'])->name('redirects.back');
            Route::post('redirects/to-route', [Feature\NavigationController::class, 'redirectToRoute'])->name('redirects.to-route');
            Route::post('redirects/external', [Feature\NavigationController::class, 'redirectExternal'])->name('redirects.external');

            Route::get('scroll-management', [Feature\NavigationController::class, 'scrollManagement'])->name('scroll-management');

            Route::get('instant-visits', [Feature\NavigationController::class, 'instantVisits'])->name('instant-visits');
            Route::get('instant-visit-target', [Feature\NavigationController::class, 'instantVisitTarget'])->name('instant-visit-target');

            Route::get('url-fragments', [Feature\NavigationController::class, 'urlFragments'])->name('url-fragments');
            Route::get('url-fragments/redirect-hash', [Feature\NavigationController::class, 'redirectWithHash']);
            Route::post('url-fragments/redirect-hash', [Feature\NavigationController::class, 'redirectWithHash']);
            Route::get('url-fragments/preserve-target', [Feature\NavigationController::class, 'preserveFragmentTarget']);
            Route::get('url-fragments/preserve-redirect', [Feature\NavigationController::class, 'preserveFragmentRedirect']);
        });

        // Data Loading
        Route::prefix('data-loading')->name('data-loading.')->group(function () {
            Route::get('deferred-props', [Feature\DataLoadingController::class, 'deferredProps'])->name('deferred-props');
            Route::get('partial-reloads', [Feature\DataLoadingController::class, 'partialReloads'])->name('partial-reloads');
            Route::get('infinite-scroll', [Feature\DataLoadingController::class, 'infiniteScroll'])->name('infinite-scroll');
            Route::get('when-visible', [Feature\DataLoadingController::class, 'whenVisible'])->name('when-visible');
            Route::get('polling', [Feature\DataLoadingController::class, 'polling'])->name('polling');
            Route::get('prop-merging', [Feature\DataLoadingController::class, 'propMerging'])->name('prop-merging');
            Route::get('once-props/{page?}', [Feature\DataLoadingController::class, 'onceProps'])->name('once-props')->where('page', '[12]');
            Route::get('optional-props', [Feature\DataLoadingController::class, 'optionalProps'])->name('optional-props');
        });

        // Prefetching
        Route::prefix('prefetching')->name('prefetching.')->group(function () {
            Route::get('link-prefetch', [Feature\PrefetchingController::class, 'linkPrefetch'])->name('link-prefetch');
            Route::get('stale-while-revalidate', [Feature\PrefetchingController::class, 'staleWhileRevalidate'])->name('stale-while-revalidate');
            Route::get('manual-prefetch', [Feature\PrefetchingController::class, 'manualPrefetch'])->name('manual-prefetch');
            Route::get('cache-management', [Feature\PrefetchingController::class, 'cacheManagement'])->name('cache-management');
        });

        // State Management
        Route::prefix('state')->name('state.')->group(function () {
            Route::get('remember', [Feature\StateController::class, 'remember'])->name('remember');
            Route::get('flash-data', [Feature\StateController::class, 'flashData'])->name('flash-data');
            Route::post('flash-data', [Feature\StateController::class, 'storeFlashData']);
            Route::post('flash-data/error', [Feature\StateController::class, 'storeFlashDataError'])->name('flash-data.error');
            Route::post('flash-data/warning', [Feature\StateController::class, 'storeFlashDataWarning'])->name('flash-data.warning');
            Route::get('shared-props', [Feature\StateController::class, 'sharedProps'])->name('shared-props');
        });

        // Layouts & Head
        Route::prefix('layouts')->name('layouts.')->group(function () {
            Route::get('persistent-layouts', [Feature\LayoutController::class, 'persistentLayouts'])->name('persistent-layouts');
            Route::get('persistent-layouts/page-2', [Feature\LayoutController::class, 'persistentLayoutsPageTwo'])->name('persistent-layouts.page-2');
            Route::get('nested-layouts', [Feature\LayoutController::class, 'nestedLayouts'])->name('nested-layouts');
            Route::get('head', [Feature\LayoutController::class, 'head'])->name('head');
            Route::get('layout-props', [Feature\LayoutController::class, 'layoutProps'])->name('layout-props');
        });

        // Events & Lifecycle
        Route::prefix('events')->name('events.')->group(function () {
            Route::get('global-events', [Feature\EventController::class, 'globalEvents'])->name('global-events');
            Route::post('global-events/action', [Feature\EventController::class, 'globalEventsAction'])->name('global-events.action');

            Route::get('once-events', [Feature\EventController::class, 'onceEvents'])->name('once-events');
            Route::post('once-events/action', [Feature\EventController::class, 'onceEventsAction'])->name('once-events.action');

            Route::get('visit-callbacks', [Feature\EventController::class, 'visitCallbacks'])->name('visit-callbacks');
            Route::post('visit-callbacks/action', [Feature\EventController::class, 'visitCallbacksAction'])->name('visit-callbacks.action');

            Route::get('progress', [Feature\EventController::class, 'progress'])->name('progress');
            Route::get('progress/slow', [Feature\EventController::class, 'progressSlow'])->name('progress.slow');

            Route::get('location-event', [Feature\EventController::class, 'locationEvent'])->name('location-event');
            Route::get('location-event/deploy', [Feature\EventController::class, 'deployNewVersion'])->name('location-event.deploy');
        });

        // Error Handling
        Route::prefix('errors')->name('errors.')->group(function () {
            Route::get('http-exceptions', [Feature\NetworkErrorController::class, 'httpExceptions'])->name('http-exceptions');
            Route::get('http-exceptions/403', [Feature\NetworkErrorController::class, 'httpException403'])->name('http-exceptions.403');
            Route::get('http-exceptions/404', [Feature\NetworkErrorController::class, 'httpException404'])->name('http-exceptions.404');
            Route::get('http-exceptions/500', [Feature\NetworkErrorController::class, 'httpException500'])->name('http-exceptions.500');
            Route::get('http-exceptions/unhandled', [Feature\NetworkErrorController::class, 'httpExceptionUnhandled'])->name('http-exceptions.unhandled');

            Route::get('network-errors', [Feature\NetworkErrorController::class, 'networkErrors'])->name('network-errors');
        });

        // Layers
        Route::prefix('layers')->name('layers.')->group(function () {
            Route::get('dialogs', [Feature\LayerController::class, 'dialogs'])->name('dialogs');
            Route::get('dialogs/{contact}', [Feature\LayerController::class, 'contactCard'])->name('dialogs.contact');
            Route::get('dialogs/{contact}/edit', [Feature\LayerController::class, 'contactEdit'])->name('dialogs.edit');
            Route::put('dialogs/{contact}', [Feature\LayerController::class, 'updateContact'])->name('dialogs.update');
            Route::post('dialogs/{contact}/favorite', [Feature\LayerController::class, 'toggleContactFavorite'])->name('dialogs.favorite');

            Route::get('stacked', [Feature\LayerController::class, 'stacked'])->name('stacked');
            Route::get('stacked/{organization}', [Feature\LayerController::class, 'organization'])->name('organization');
            Route::get('stacked/{organization}/{contact}', [Feature\LayerController::class, 'organizationContact'])->name('organization.contact');
            Route::post('stacked/{organization}/{contact}/notes', [Feature\LayerController::class, 'storeNote'])->name('organization.notes');

            Route::get('local', [Feature\LayerController::class, 'local'])->name('local');
            Route::post('local/{contact}/favorite', [Feature\LayerController::class, 'toggleFavorite'])->name('local.favorite');

            Route::get('inside', [Feature\LayerController::class, 'inside'])->name('inside');
            Route::get('inside/panel', [Feature\LayerController::class, 'insidePanel'])->name('inside.panel');
            Route::get('inside/child', [Feature\LayerController::class, 'insideChild'])->name('inside.child');
            Route::post('inside/close', [Feature\LayerController::class, 'insideClose'])->name('inside.close');

            Route::get('features', [Feature\LayerController::class, 'features'])->name('features');
            Route::get('features/panel', [Feature\LayerController::class, 'featuresPanel'])->name('features.panel');

            Route::get('cold', [Feature\LayerController::class, 'cold'])->name('cold');
            Route::get('cold/panel', [Feature\LayerController::class, 'coldPanel'])->name('cold.panel');

            Route::get('wizard', [Feature\LayerController::class, 'wizard'])->name('wizard');
            Route::get('wizard/help', [Feature\LayerController::class, 'wizardHelp'])->name('wizard.help');
            Route::get('wizard/{step}', [Feature\LayerController::class, 'wizardStep'])->name('wizard.step')->whereIn('step', ['details', 'access', 'review']);

            Route::get('detours', [Feature\LayerController::class, 'detours'])->name('detours');
            Route::get('detours/secret', [Feature\LayerController::class, 'detour'])->name('detour');
            Route::get('detours/confirm', [Feature\LayerController::class, 'detourConfirm'])->name('detour.confirm');
            Route::post('detours/confirm', [Feature\LayerController::class, 'detourComplete'])->name('detour.complete');
            Route::post('detours/forget', [Feature\LayerController::class, 'detourForget'])->name('detour.forget');
            Route::get('detours/denied', [Feature\LayerController::class, 'detourDenied'])->name('detour.denied');
            Route::get('detours/missing', [Feature\LayerController::class, 'detourMissing'])->name('detour.missing');
            Route::get('detours/broken', [Feature\LayerController::class, 'detourBroken'])->name('detour.broken');
        });

        // HTTP
        Route::prefix('http')->name('http.')->group(function () {
            Route::get('use-http', [Feature\HttpController::class, 'useHttp'])->name('use-http');
            Route::post('use-http/api', [Feature\HttpController::class, 'useHttpApi'])->name('use-http.api');
        });
    });
});
