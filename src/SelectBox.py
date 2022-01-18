import ui


def select(list, current_id, dim=(500, 500)):
    popup = ui.TableView(frame=(0, 0, dim[0], dim[1]))

    def on_selected(sender):
        nonlocal current_id
        current_id = sender.selected_row
        popup.close()

    popup.data_source = ui.ListDataSource(list)
    popup.delegate = popup.data_source
    popup.data_source.action = on_selected
    popup.selected_row = (0, current_id)
    popup.present('sheet')
    popup.wait_modal()

    return current_id


if __name__ == '__main__':
    print(select(range(0, 30), 5))
