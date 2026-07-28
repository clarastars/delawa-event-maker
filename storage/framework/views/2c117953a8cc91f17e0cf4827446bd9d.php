<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e($title ?? 'Admin'); ?> - Delawa - ديلاوة</title>
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('favicon_16x16.png')); ?>">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('favicon_32x32.png')); ?>">
        <link rel="icon" type="image/png" sizes="48x48" href="<?php echo e(asset('favicon_48x48.png')); ?>">
        <link rel="icon" type="image/png" sizes="64x64" href="<?php echo e(asset('favicon_64x64.png')); ?>">
        <link rel="icon" type="image/png" sizes="128x128" href="<?php echo e(asset('favicon_128x128.png')); ?>">
        <link rel="icon" type="image/png" sizes="256x256" href="<?php echo e(asset('favicon_256x256.png')); ?>">
        <?php echo app('Illuminate\Foundation\Vite')->fonts(); ?>
        <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
            <?php echo $__env->yieldPushContent('vite'); ?>
        <?php endif; ?>
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-950 antialiased">
        <div class="mx-auto min-h-screen w-full max-w-6xl px-6 py-8">
            <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3 text-2xl font-black text-[#4E2E36]">
                        <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Delawa" class="h-10 w-10 rounded-full border-2 border-white shadow-sm">
                        <span>Delawa Admin <span class="font-normal text-base text-slate-500">ديلاوة</span></span>
                    </a>
                    <p class="text-sm text-slate-500">Manage Delawa events, vouchers, and invite contacts.</p>
                </div>

                <?php if(auth()->guard()->check()): ?>
                    <nav class="flex flex-wrap items-center gap-3 text-sm font-semibold">
                        <a href="<?php echo e(route('admin.events.index')); ?>" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Events</a>
                        <a href="<?php echo e(route('admin.vouchers.index')); ?>" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Vouchers</a>
                        <a href="<?php echo e(route('admin.contacts.index')); ?>" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Contacts</a>
                        <a href="<?php echo e(route('admin.reviews.index')); ?>" class="rounded-full bg-white px-4 py-2 text-slate-700 shadow-sm ring-1 ring-slate-200 hover:text-[#4E2E36]">Reviews</a>
                        <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="rounded-full bg-slate-950 px-4 py-2 text-white">Log out</button>
                        </form>
                    </nav>
                <?php endif; ?>
            </header>

            <?php if(session('status')): ?>
                <div class="mb-6 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            <?php echo e($slot); ?>

        </div>
    </body>
</html>
<?php /**PATH /Users/shafiqal-shaar/projects/delawa-event-maker/resources/views/components/admin/layout.blade.php ENDPATH**/ ?>