@extends('layouts.app')

@section('title', '日程安排')

@section('content')
    <div class="container mx-auto max-w-7xl" x-data="{ showAlert: false, type: 'success', message: 'Test success message here !' }"
        x-on:alert="
            ({ detail }) => {
                type = detail[0].type;
                message = detail[0].message;
                showAlert = true;
                setTimeout(() => showAlert = false, 3000);
            }
        ">
        <div
            x-show="showAlert"
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-700"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            role="alert"
            class="alert fixed z-10 top-20 w-1/3 left-1/2 -translate-x-1/2"
            :class="{
                'alert-success': type === 'success',
                'alert-error': type === 'error',
                'alert-warning': type === 'warning',
            }">
            <svg x-show="type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current"
                fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg x-show="type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none"
                viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg x-show="type === 'warning'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current"
                fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>

            <span x-text="message"></span>
        </div>
        <!-- 步骤条 -->
        <div class="mb-6">
            <ul class="steps w-full">
                <li class="step step-primary">创建运动会</li>
                <li class="step step-primary">参赛年级</li>
                <li class="step step-primary">运动员报名</li>
                <li class="step step-primary">径赛分组</li>
                <li class="step step-primary">日程安排</li>
                <li class="step">总览</li>
            </ul>
        </div>

        <div class="bg-white p-6 rounded-md shadow-md">
            <!-- 顶部操作栏 -->
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-bold">日程安排 - 径赛</h3>
            </div>
            <livewire:schedule-list :$competition />
            <!-- 底部导航 -->
            <div class="w-full flex justify-end mt-8">
                <a href="{{ route('competitions.heats.index', $competition) }}" class="btn mr-2">
                    上一步
                </a>
                <a href="{{ route('competitions.schedules.index-field', $competition) }}" class="btn btn-primary">
                    下一步
                </a>
            </div>
        </div>
    </div>
@endsection
