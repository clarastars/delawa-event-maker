<?php if (isset($component)) { $__componentOriginal7651faf8e4a1e278424aad70c82de3ba = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7651faf8e4a1e278424aad70c82de3ba = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layout','data' => ['title' => 'Contacts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Contacts']); ?>
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Contacts</h1>
                <p class="mt-2 text-sm text-slate-500">Search invitees and manage voucher assignments.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="<?php echo e(route('admin.contacts.export', request()->only('search'))); ?>" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Export CSV
                </a>
                <a href="<?php echo e(route('admin.contacts.upload.create')); ?>" class="rounded-2xl border border-[#7D4651] bg-white px-5 py-3 text-sm font-bold text-[#4E2E36] hover:bg-[#7D4651]/5">
                    Add / Import
                </a>
            </div>
        </div>

        <form method="GET" action="<?php echo e(route('admin.contacts.index')); ?>" class="mb-6">
            <div class="flex flex-col gap-3 sm:flex-row">
                <input
                    type="search"
                    name="search"
                    value="<?php echo e($search); ?>"
                    placeholder="Search by name, email, or phone..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                >
                <button class="shrink-0 rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                    Search
                </button>
                <?php if($search !== ''): ?>
                    <a href="<?php echo e(route('admin.contacts.index')); ?>" class="shrink-0 rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-bold text-slate-700">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="mb-6 flex flex-wrap gap-2 text-xs font-semibold text-slate-500">
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-800 ring-1 ring-emerald-100">Full balance</span>
            <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-800 ring-1 ring-amber-100">Partially used</span>
            <span class="rounded-full bg-red-50 px-3 py-1 text-red-700 ring-1 ring-red-100">Fully used</span>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600 ring-1 ring-slate-200">Not synced</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Vouchers</th>
                        <th class="px-4 py-3">Activated</th>
                        <th class="px-4 py-3">Card value</th>
                        <th class="px-4 py-3">Remaining</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-950"><?php echo e($contact->name ?: '—'); ?></td>
                            <td class="px-4 py-4 text-slate-600" dir="ltr"><?php echo e($contact->phone); ?></td>
                            <td class="px-4 py-4 text-slate-600"><?php echo e($contact->email ?: '—'); ?></td>
                            <td class="px-4 py-4">
                                <?php if($contact->vouchers->isNotEmpty()): ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $contact->vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if (isset($component)) { $__componentOriginal61ecfa46f35d58e0532bb447fa736d99 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal61ecfa46f35d58e0532bb447fa736d99 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.voucher-balance-crumb','data' => ['voucher' => $voucher]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.voucher-balance-crumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['voucher' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($voucher)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal61ecfa46f35d58e0532bb447fa736d99)): ?>
<?php $attributes = $__attributesOriginal61ecfa46f35d58e0532bb447fa736d99; ?>
<?php unset($__attributesOriginal61ecfa46f35d58e0532bb447fa736d99); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal61ecfa46f35d58e0532bb447fa736d99)): ?>
<?php $component = $__componentOriginal61ecfa46f35d58e0532bb447fa736d99; ?>
<?php unset($__componentOriginal61ecfa46f35d58e0532bb447fa736d99); ?>
<?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4">
                                <?php if($contact->isActivated()): ?>
                                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800" title="<?php echo e($contact->activated_at->format('Y-m-d H:i')); ?>">
                                        <?php echo e($contact->activated_at->format('Y-m-d H:i')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Not yet</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <?php if($contact->vouchers->isNotEmpty()): ?>
                                    <?php echo e(number_format((float) $contact->vouchers->sum('balance'), 2)); ?>

                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <?php if($contact->vouchers->whereNotNull('remaining_balance')->isNotEmpty()): ?>
                                    <?php echo e(number_format((float) $contact->vouchers->whereNotNull('remaining_balance')->sum('remaining_balance'), 2)); ?>

                                <?php elseif($contact->vouchers->isNotEmpty()): ?>
                                    <span class="text-xs text-slate-400">Not synced</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="<?php echo e(route('admin.contacts.show', $contact)); ?>" class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-[#7D4651]/10 hover:text-[#4E2E36]">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                <?php if($search !== ''): ?>
                                    No contacts found for "<?php echo e($search); ?>".
                                <?php else: ?>
                                    No contacts yet. <a href="<?php echo e(route('admin.contacts.upload.create')); ?>" class="font-semibold text-[#4E2E36] underline">Add your first contact</a>.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($contacts->links()); ?>

        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7651faf8e4a1e278424aad70c82de3ba)): ?>
<?php $attributes = $__attributesOriginal7651faf8e4a1e278424aad70c82de3ba; ?>
<?php unset($__attributesOriginal7651faf8e4a1e278424aad70c82de3ba); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7651faf8e4a1e278424aad70c82de3ba)): ?>
<?php $component = $__componentOriginal7651faf8e4a1e278424aad70c82de3ba; ?>
<?php unset($__componentOriginal7651faf8e4a1e278424aad70c82de3ba); ?>
<?php endif; ?>
<?php /**PATH /Users/shafiqal-shaar/projects/delawa-event-maker/resources/views/admin/contacts/index.blade.php ENDPATH**/ ?>