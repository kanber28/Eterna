@extends('dashboard.layouts.master')
@section('title','İşlem Ağacı')
@section('content')
    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
        }

        ul2 {
            --col-gap: 2rem;
            --row-gap: 2rem;
            --line-w: 0.25rem;
            display: grid;
            grid-template-columns: var(--line-w) 1fr;
            grid-auto-columns: max-content;
            column-gap: var(--col-gap);
            list-style: none;
            width: min(60rem, 90%);
            margin-inline: auto;
        }

        /* line */
        ul2::before {
            content: "";
            grid-column: 1;
            grid-row: 1 / span 20;
            background: rgb(225, 225, 225);
            border-radius: calc(var(--line-w) / 2);
        }

        /* columns*/

        /* row gaps */
        ul2 li:not(:last-child) {
            margin-bottom: var(--row-gap);
        }

        /* card */
        ul2 li {
            grid-column: 2;
            --inlineP: 1.5rem;
            margin-inline: var(--inlineP);
            grid-row: span 2;
            display: grid;
            grid-template-rows: min-content min-content min-content;
        }

        /* date */
        ul2 li .date {
            --dateH: 3rem;
            height: var(--dateH);
            margin-inline: calc(var(--inlineP) * -1);

            text-align: center;
            background-color: var(--accent-color);

            color: white;
            font-size: 1.25rem;
            font-weight: 700;

            display: grid;
            place-content: center;
            position: relative;

            border-radius: calc(var(--dateH) / 2) 0 0 calc(var(--dateH) / 2);
        }

        /* date flap */
        ul2 li .date::before {
            content: "";
            width: var(--inlineP);
            aspect-ratio: 1;
            background: var(--accent-color);
            background-image: linear-gradient(rgba(0, 0, 0, 0.2) 100%, transparent);
            position: absolute;
            top: 100%;

            clip-path: polygon(0 0, 100% 0, 0 100%);
            right: 0;
        }

        /* circle */
        ul2 li .date::after {
            content: "";
            position: absolute;
            width: 2rem;
            aspect-ratio: 1;
            background: var(--bgColor);
            border: 0.3rem solid var(--accent-color);
            border-radius: 50%;
            top: 50%;

            transform: translate(50%, -50%);
            right: calc(100% + var(--col-gap) + var(--line-w) / 2);
        }

        /* title descr */
        ul2 li .title,
        ul2 li .descr {
            background: var(--bgColor);
            position: relative;
            padding-inline: 1.5rem;
        }
        ul2 li .title {
            overflow: hidden;
            padding-block-start: 1.5rem;
            padding-block-end: 1rem;
            font-weight: 500;
        }
        ul2 li .descr {
            padding-block-end: 1.5rem;
            font-weight: 300;
        }

        /* shadows */
        ul2 li .title::before,
        ul2 li .descr::before {
            content: "";
            position: absolute;
            width: 90%;
            height: 0.5rem;
            background: rgba(0, 0, 0, 0.5);
            left: 50%;
            border-radius: 50%;
            filter: blur(4px);
            transform: translate(-50%, 50%);
        }
        ul2 li .title::before {
            bottom: calc(100% + 0.125rem);
        }

        ul2 li .descr::before {
            z-index: -1;
            bottom: 0.25rem;
        }

        @media (min-width: 40rem) {
            ul2 {
                grid-template-columns: 1fr var(--line-w) 1fr;
            }
            ul2::before {
                grid-column: 2;
            }
            ul2 li:nth-child(odd) {
                grid-column: 1;
            }
            ul2 li:nth-child(even) {
                grid-column: 3;
            }

            /* start second card */
            ul2 li:nth-child(2) {
                grid-row: 2/4;
            }

            ul2 li:nth-child(odd) .date::before {
                clip-path: polygon(0 0, 100% 0, 100% 100%);
                left: 0;
            }

            ul2 li:nth-child(odd) .date::after {
                transform: translate(-50%, -50%);
                left: calc(100% + var(--col-gap) + var(--line-w) / 2);
            }
            ul2 li:nth-child(odd) .date {
                border-radius: 0 calc(var(--dateH) / 2) calc(var(--dateH) / 2) 0;
            }
        }

        .credits {
            margin-top: 1rem;
            text-align: right;
        }
        .credits a {
            color: var(--color);
        }

        body {
            overflow: hidden;
        }

    </style>
    <div class="col-lg-12 layout-spacing">
        <div class="statbox widget box box-shadow mb-4" style="overflow: auto; height: 850px;">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <div class="makeitSticky z">
                            {{--                            <h4>İşlem Ağacı</h4>--}}
                        </div>
                    </div>
                </div>
            </div>

            <h2>İşlem Ağacı</h2>
            <ul2 style=" grid-auto-rows: 14%;" >
                @if($tree->getProcess)
                    @foreach($tree->getProcess as $process)
                        <li style="--accent-color:#677ada">
                            <div class="date">{{$process->title}}</div>
                            <div class="title">{{\Carbon\Carbon::parse($process->created_at)->format('H:i:s - d.m.Y')}}</div>
                            <div class="descr">{!! $process->process !!}</div>
                        </li>
                    @endforeach
                @endif
            </ul2>
        </div>
    </div>
@endsection

@section('style')
@endsection

@section('js')
@endsection
