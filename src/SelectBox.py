import ui


def select(list, current_id, sortable=False, dim=(500, 500)):
    edit_btn = ui.ButtonItem('Edit')
    done_btn = ui.ButtonItem('Done')
    popup = ui.TableView(frame=(0, 0, dim[0], dim[1]))

    def on_selected(sender):
        nonlocal current_id
        current_id = sender.selected_row
        popup.close()

    def on_edit(sender):
        popup.right_button_items = [done_btn]
        popup.set_editing(True, True)

    def on_done(sender):
        nonlocal list
        popup.set_editing(False, True)
        popup.right_button_items = [edit_btn]
        list = popup.data_source.items

    edit_btn.action = on_edit
    done_btn.action = on_done
    popup.data_source = ui.ListDataSource(list)
    popup.delegate = popup.data_source
    popup.data_source.action = on_selected
    popup.data_source.move_enabled = True
    popup.data_source.delete_enabled = False
    popup.selected_row = (0, current_id)
    popup.allows_selection_during_editing = False

    if sortable:
        on_done(None)

    popup.present('sheet')
    popup.wait_modal()

    return (current_id, list)


if __name__ == '__main__':
    print(select(range(0, 30), 5, True))
