@extends('layouts.app')

@section('style')
th:first-letter {
    text-transform:capitalize;
}
@endsection

@section('content')
    <h1 class="col-12 text-center">
        {!! $item->symbol . ', ' . $item->name !!}
    </h1>

    <div class='col-12'>
        <stock-prices></stock-prices>
    </div>
@endsection

@section('javascript')
    Vue.component('stock-prices', {
        template: `
            <table class='table table-striped table-bordered table-sm'>
                <thead class='thead-dark'>
                    <tr>
                        <th v-for='(label, index) in labels' :key='index' v-html='label'></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for='(price, index) in prices' :key='index'>
                        <td v-for='(label, index) in labels' :key='index'>
                            @{{ price[label] }}
                        </td>
                    </tr>
                </tbody>
                
            </table>
        `,
        data() {
            return {
                labels: [],
                prices: []
            }
        },
        mounted() {
            // Fetch audio types
            fetch('/stock-price/{{ $item->symbol }}')
                .then(response => response.json())
                .then(json => {
                    this.labels = Object.keys(json[0])
                                    .filter(label => {
                                        switch (label) {
                                            case 'symbol':
                                            case 'created_at':
                                            case 'updated_at':
                                                return false;
                                            default:
                                                return true;
                                        }
                                    });
                    this.prices = json;
                })
                .catch(error => {
                    console.error("Encountered a problem retrieving prices: " + error);
            });
        }
    });

    const app = new Vue({
        el: '#app'
    });
@endsection