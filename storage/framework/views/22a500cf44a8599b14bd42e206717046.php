<?php if (isset($component)) { $__componentOriginal7651faf8e4a1e278424aad70c82de3ba = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7651faf8e4a1e278424aad70c82de3ba = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layout','data' => ['title' => 'Reviews']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reviews']); ?>
    <section class="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black">Reviews</h1>
                <p class="mt-2 text-sm text-slate-500">View feedback and experiences submitted by users.</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Experience</th>
                        <th class="px-4 py-3">Submitted At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-4 font-semibold text-slate-950"><?php echo e($review->event->name ?? '—'); ?></td>
                            <td class="px-4 py-4">
                                <?php if($review->contact): ?>
                                    <a href="<?php echo e(route('admin.contacts.show', $review->contact)); ?>" class="text-[#4E2E36] hover:underline font-semibold">
                                        <?php echo e($review->contact->name ?: $review->contact->phone); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-500">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-slate-600 whitespace-pre-wrap max-w-md"><?php echo e($review->experience); ?></td>
                            <td class="px-4 py-4 text-slate-500"><?php echo e($review->created_at->format('M d, Y H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                                No reviews have been submitted yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <?php echo e($reviews->links()); ?>

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
<?php endif; ?><?php /**PATH /Users/shafiqal-shaar/projects/delawa-event-maker/resources/views/admin/reviews/index.blade.php ENDPATH**/ ?>