@extends('layouts.app')

@section('title', '创建年级')

@section('content')
<div class="container mx-auto max-w-2xl">
    <h2 class="text-3xl font-bold mb-6">创建年级</h2>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <form action="{{ route('competitions.grades.store', $competition) }}" method="POST">
                @csrf
                
                <div class="form-control w-full">
                    <label class="label">
                        <span class="label-text font-semibold">年级名称 *</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name') }}" 
                           class="input input-bordered w-full @error('name') input-error @enderror" 
                           placeholder="例如：一年级、初一、高一"
                           required
                           autofocus>
                    <label class="label">
                        <span class="label-text-alt">建议使用标准格式，如：一年级、二年级、三年级</span>
                    </label>
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <!-- 示例提示 -->
                <div class="alert alert-info mt-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold">命名示例：</p>
                        <p>小学：一年级、二年级、三年级、四年级、五年级、六年级</p>
                        <p>初中：初一、初二、初三 或 七年级、八年级、九年级</p>
                        <p>高中：高一、高二、高三</p>
                    </div>
                </div>

                <div class="card-actions justify-end mt-6">
                    <a href="{{ route('competitions.grades.index', $competition) }}" class="btn btn-ghost">取消</a>
                    <button type="submit" class="btn btn-primary">创建年级</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
