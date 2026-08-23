# Filament Infinite Select

A Filament form component that loads select options one page at a time as the user scrolls. Use it when a select can contain hundreds or thousands of options and loading all of them at once is too slow.

## How it works

You provide a closure that receives an offset, a limit, and the current search term, and returns one page of options. The component handles the rest:

- The first page loads when the dropdown opens (or renders server side with `preloadFirstPage()`)
- Scrolling near the bottom of the list fetches the next page and appends it
- Search input queries your closure with debouncing, resets to page one, and replaces the list
- Saved values resolve their labels through `getOptionLabelUsing()` or `getOptionLabelsUsing()`, so they display even when they are not in the loaded pages
- The scroll position is kept when the dropdown is closed and reopened

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2 or higher |
| Laravel | 11.28 or higher |
| Filament Forms | 4.x or 5.x |

The component works in panels and in standalone Livewire applications that use Filament forms.

## Credits

- [Punyapal Shah](https://github.com/mrpunyapal)
- [All contributors](https://github.com/mrpunyapal/filament-infinite-select/graphs/contributors)

## License

The MIT License (MIT). See [License](https://github.com/mrpunyapal/filament-infinite-select/blob/main/LICENSE.md) for details.
