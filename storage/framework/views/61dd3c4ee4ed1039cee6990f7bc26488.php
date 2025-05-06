<?php if (isset($component)) { $__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card','data' => ['cols' => $cols,'rows' => $rows,'class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['cols' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cols),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rows),'class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>
    <?php if (isset($component)) { $__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.card-header','data' => ['name' => match ($this->type) {
            'requests' => 'Top 10 Users Making Requests',
            'slow_requests' => 'Top 10 Users Experiencing Slow Endpoints',
            'jobs' => 'Top 10 Users Dispatching Jobs',
            default => 'Application Usage'
        },'xBind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => ''.e($this->usage === 'slow_requests' ? (is_array($slowRequestsConfig['threshold']) ? '' : $slowRequestsConfig['threshold'].'ms threshold, ') : '').'past '.e($this->periodForHumans()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::card-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(match ($this->type) {
            'requests' => 'Top 10 Users Making Requests',
            'slow_requests' => 'Top 10 Users Experiencing Slow Endpoints',
            'jobs' => 'Top 10 Users Dispatching Jobs',
            default => 'Application Usage'
        }),'x-bind:title' => '`Time: '.e(number_format($time)).'ms; Run at: ${formatDate(\''.e($runAt).'\')};`','details' => ''.e($this->usage === 'slow_requests' ? (is_array($slowRequestsConfig['threshold']) ? '' : $slowRequestsConfig['threshold'].'ms threshold, ') : '').'past '.e($this->periodForHumans()).'']); ?>
         <?php $__env->slot('icon', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => 'pulse::icons.' . match ($this->type) {
                'requests' => 'arrow-trending-up',
                'slow_requests' => 'clock',
                'jobs' => 'scale',
                default => 'cursor-arrow-rays'
            }] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\DynamicComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <!--[if BLOCK]><![endif]--><?php if(! $this->type): ?>
                <?php if (isset($component)) { $__componentOriginal1b3e53436ecefa44bef3b401e2b29716 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1b3e53436ecefa44bef3b401e2b29716 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.select','data' => ['wire:model.live' => 'usage','id' => 'select-usage-by','label' => 'Top 10 users','options' => [
                        'requests' => 'making requests',
                        'slow_requests' => 'experiencing slow endpoints',
                        'jobs' => 'dispatching jobs',
                    ],'class' => 'flex-1','@change' => 'loading = true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.live' => 'usage','id' => 'select-usage-by','label' => 'Top 10 users','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
                        'requests' => 'making requests',
                        'slow_requests' => 'experiencing slow endpoints',
                        'jobs' => 'dispatching jobs',
                    ]),'class' => 'flex-1','@change' => 'loading = true']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1b3e53436ecefa44bef3b401e2b29716)): ?>
<?php $attributes = $__attributesOriginal1b3e53436ecefa44bef3b401e2b29716; ?>
<?php unset($__attributesOriginal1b3e53436ecefa44bef3b401e2b29716); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1b3e53436ecefa44bef3b401e2b29716)): ?>
<?php $component = $__componentOriginal1b3e53436ecefa44bef3b401e2b29716; ?>
<?php unset($__componentOriginal1b3e53436ecefa44bef3b401e2b29716); ?>
<?php endif; ?>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <!--[if BLOCK]><![endif]--><?php if($this->usage === 'slow_requests' && is_array($slowRequestsConfig['threshold'])): ?>
                <?php
                    $message = 'You have per-route thresholds configured.';
                ?>
                <button title="<?php echo e($message); ?>" @click="alert(<?php echo \Illuminate\Support\Js::from($message)->toHtml() ?>)">
                    <?php if (isset($component)) { $__componentOriginal95da57cee6437fd1b7658be6198e84c4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95da57cee6437fd1b7658be6198e84c4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.icons.information-circle','data' => ['class' => 'w-5 h-5 stroke-gray-400 dark:stroke-gray-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::icons.information-circle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-5 h-5 stroke-gray-400 dark:stroke-gray-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95da57cee6437fd1b7658be6198e84c4)): ?>
<?php $attributes = $__attributesOriginal95da57cee6437fd1b7658be6198e84c4; ?>
<?php unset($__attributesOriginal95da57cee6437fd1b7658be6198e84c4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95da57cee6437fd1b7658be6198e84c4)): ?>
<?php $component = $__componentOriginal95da57cee6437fd1b7658be6198e84c4; ?>
<?php unset($__componentOriginal95da57cee6437fd1b7658be6198e84c4); ?>
<?php endif; ?>
                </button>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
         <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89)): ?>
<?php $attributes = $__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89; ?>
<?php unset($__attributesOriginal7ce092db05b46b96a8ad5ab4b8902a89); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89)): ?>
<?php $component = $__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89; ?>
<?php unset($__componentOriginal7ce092db05b46b96a8ad5ab4b8902a89); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginalbea25b6319928d1c693b59ced602f799 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbea25b6319928d1c693b59ced602f799 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.scroll','data' => ['expand' => $expand,'wire:poll.5s' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::scroll'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['expand' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($expand),'wire:poll.5s' => '']); ?>
        <!--[if BLOCK]><![endif]--><?php if($userRequestCounts->isEmpty()): ?>
            <?php if (isset($component)) { $__componentOriginal5fa7cfb847383b1e105a397b36250360 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fa7cfb847383b1e105a397b36250360 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.no-results','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::no-results'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fa7cfb847383b1e105a397b36250360)): ?>
<?php $attributes = $__attributesOriginal5fa7cfb847383b1e105a397b36250360; ?>
<?php unset($__attributesOriginal5fa7cfb847383b1e105a397b36250360); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fa7cfb847383b1e105a397b36250360)): ?>
<?php $component = $__componentOriginal5fa7cfb847383b1e105a397b36250360; ?>
<?php unset($__componentOriginal5fa7cfb847383b1e105a397b36250360); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="grid grid-cols-1 @lg:grid-cols-2 @3xl:grid-cols-3 @6xl:grid-cols-4 gap-2">
                <?php
                    $sampleRate = match($this->usage) {
                        'requests' => $userRequestsConfig['sample_rate'],
                        'slow_requests' => $slowRequestsConfig['sample_rate'],
                        'jobs' => $jobsConfig['sample_rate'],
                    };
                ?>

                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $userRequestCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userRequestCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginalccdd171d8948f030bb20fc96cf400e8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalccdd171d8948f030bb20fc96cf400e8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'pulse::components.user-card','data' => ['wire:key' => ''.e($userRequestCount->key).'','user' => $userRequestCount->user]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('pulse::user-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($userRequestCount->key).'','user' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($userRequestCount->user)]); ?>
                         <?php $__env->slot('stats', null, []); ?> 
                            <!--[if BLOCK]><![endif]--><?php if($sampleRate < 1): ?>
                                <span title="Sample rate: <?php echo e($sampleRate); ?>, Raw value: <?php echo e(number_format($userRequestCount->count)); ?>">~<?php echo e(number_format($userRequestCount->count * (1 / $sampleRate))); ?></span>
                            <?php else: ?>
                                <?php echo e(number_format($userRequestCount->count)); ?>

                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                         <?php $__env->endSlot(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalccdd171d8948f030bb20fc96cf400e8b)): ?>
<?php $attributes = $__attributesOriginalccdd171d8948f030bb20fc96cf400e8b; ?>
<?php unset($__attributesOriginalccdd171d8948f030bb20fc96cf400e8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalccdd171d8948f030bb20fc96cf400e8b)): ?>
<?php $component = $__componentOriginalccdd171d8948f030bb20fc96cf400e8b; ?>
<?php unset($__componentOriginalccdd171d8948f030bb20fc96cf400e8b); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbea25b6319928d1c693b59ced602f799)): ?>
<?php $attributes = $__attributesOriginalbea25b6319928d1c693b59ced602f799; ?>
<?php unset($__attributesOriginalbea25b6319928d1c693b59ced602f799); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbea25b6319928d1c693b59ced602f799)): ?>
<?php $component = $__componentOriginalbea25b6319928d1c693b59ced602f799; ?>
<?php unset($__componentOriginalbea25b6319928d1c693b59ced602f799); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0)): ?>
<?php $attributes = $__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0; ?>
<?php unset($__attributesOriginaldf0c1f9d71acfa8b3005f4638b1a29f0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0)): ?>
<?php $component = $__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0; ?>
<?php unset($__componentOriginaldf0c1f9d71acfa8b3005f4638b1a29f0); ?>
<?php endif; ?>
<?php /**PATH G:\Development\Maniruzzaman Akash\laradashboard\vendor\laravel\pulse\src/../resources/views/livewire/usage.blade.php ENDPATH**/ ?>