<?php if (isset($component)) { $__componentOriginal7651faf8e4a1e278424aad70c82de3ba = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7651faf8e4a1e278424aad70c82de3ba = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layout','data' => ['title' => 'Contact']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Contact']); ?>
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="<?php echo e(route('admin.contacts.index')); ?>" class="text-sm font-semibold text-[#4E2E36] hover:underline">&larr; Back to contacts</a>
                <h1 class="mt-2 text-3xl font-black"><?php echo e($contact->name ?: 'Unnamed contact'); ?></h1>
                <p class="mt-2 text-sm text-slate-500">View details and assign a voucher.</p>
            </div>
            <form
                method="POST"
                action="<?php echo e(route('admin.contacts.destroy', $contact)); ?>"
                onsubmit="return confirm('Delete this contact? Any assigned voucher will be unassigned.')"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="rounded-2xl bg-red-50 px-5 py-3 text-sm font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                    Delete contact
                </button>
            </form>
        </div>

        <div class="mb-8 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Contact details</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-semibold text-slate-500">Name</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-950"><?php echo e($contact->name ?: '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Phone</dt>
                        <dd class="mt-1">
                            <form method="POST" action="<?php echo e(route('admin.contacts.update', $contact)); ?>" class="space-y-3">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <input
                                        type="text"
                                        name="phone"
                                        value="<?php echo e(old('phone', $contact->phone)); ?>"
                                        required
                                        dir="ltr"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-950 outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                                    >
                                    <button class="shrink-0 rounded-2xl bg-[#7D4651] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                                        Save phone
                                    </button>
                                </div>
                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="text-sm font-medium text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </form>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Email</dt>
                        <dd class="mt-1 text-slate-950"><?php echo e($contact->email ?: '—'); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Added</dt>
                        <dd class="mt-1 text-slate-950"><?php echo e($contact->created_at?->format('Y-m-d H:i')); ?></dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-slate-500">Activated</dt>
                        <dd class="mt-1">
                            <?php if($contact->isActivated()): ?>
                                <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-800">
                                    <?php echo e($contact->activated_at->format('Y-m-d H:i')); ?>

                                </span>
                            <?php else: ?>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Not yet</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 p-6">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Assigned vouchers (<?php echo e($contact->vouchers->count()); ?>)</h2>

                <?php if($contact->vouchers->isEmpty()): ?>
                    <p class="mt-4 text-sm text-amber-800">No vouchers assigned yet.</p>
                <?php else: ?>
                    <ul class="mt-4 space-y-4">
                        <?php $__currentLoopData = $contact->vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xl font-black text-[#4E2E36]"><?php echo e($voucher->voucher_id); ?></p>
                                        <p class="mt-1 text-sm text-slate-600">
                                            <?php echo e($voucher->event?->name ?? 'No event'); ?>

                                            &middot; Card value <span class="font-bold"><?php echo e(number_format((float) $voucher->balance, 2)); ?></span>
                                            &middot; Remaining
                                            <?php if($voucher->remaining_balance !== null): ?>
                                                <span class="font-bold"><?php echo e(number_format((float) $voucher->remaining_balance, 2)); ?></span>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400">Not synced</span>
                                            <?php endif; ?>
                                            &middot; <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold"><?php echo e(ucfirst($voucher->status)); ?></span>
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Valid: <?php echo e($voucher->creation_date?->format('Y-m-d')); ?>

                                            <?php if($voucher->expiry_date): ?>
                                                &mdash; <?php echo e($voucher->expiry_date->format('Y-m-d')); ?>

                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <form
                                        method="POST"
                                        action="<?php echo e(route('admin.contacts.unassign-voucher', [$contact, $voucher])); ?>"
                                        onsubmit="return confirm('Unassign voucher <?php echo e($voucher->voucher_id); ?> from this contact?')"
                                    >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="rounded-full bg-red-50 px-3 py-2 text-xs font-bold text-red-700 ring-1 ring-red-200 hover:bg-red-100">
                                            Unassign
                                        </button>
                                    </form>
                                </div>
                                <svg data-voucher-barcode="<?php echo e($voucher->voucher_id); ?>" class="mx-auto mt-4 h-12 w-full max-w-[14rem]" aria-hidden="true"></svg>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                <?php endif; ?>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">Assign another voucher</h3>

                    <?php if($availableVouchers->isNotEmpty()): ?>
                        <form method="POST" action="<?php echo e(route('admin.contacts.assign-voucher', $contact)); ?>" class="mt-4 space-y-4">
                            <?php echo csrf_field(); ?>
                            <div>
                                <label for="voucher_id" class="mb-2 block text-sm font-semibold text-slate-700">Select voucher</label>
                                <select
                                    id="voucher_id"
                                    name="voucher_id"
                                    required
                                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                                >
                                    <option value="">Choose a voucher...</option>
                                    <?php $__currentLoopData = $availableVouchers->groupBy(fn ($voucher) => $voucher->event?->name ?? 'No event'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eventName => $eventVouchers): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <optgroup label="<?php echo e($eventName); ?>">
                                            <?php $__currentLoopData = $eventVouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($voucher->id); ?>" <?php if(old('voucher_id') == $voucher->id): echo 'selected'; endif; ?>>
                                                    <?php echo e($voucher->voucher_id); ?> — <?php echo e(number_format((float) $voucher->balance, 2)); ?> (expires <?php echo e($voucher->expiry_date?->format('Y-m-d') ?? 'n/a'); ?>)
                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </optgroup>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['voucher_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm font-medium text-red-600"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <button class="rounded-2xl bg-[#7D4651] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#7D4651]/25 hover:bg-[#6A3A44]">
                                Assign voucher
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="mt-4 text-sm text-slate-500">
                            No unassigned active vouchers available.
                            <a href="<?php echo e(route('admin.vouchers.upload.create')); ?>" class="font-semibold text-[#4E2E36] underline">Upload vouchers</a> first.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if($contact->vouchers->isNotEmpty()): ?>
            <?php $__env->startPush('vite'); ?>
                <?php echo app('Illuminate\Foundation\Vite')(['resources/js/event-vouchers.js']); ?>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>
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
<?php /**PATH /Users/shafiqal-shaar/projects/delawa-event-maker/resources/views/admin/contacts/show.blade.php ENDPATH**/ ?>