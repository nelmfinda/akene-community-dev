import React from 'react';
import {ItemCollection} from './ItemCollection';
import {render, screen, fireEvent} from '../../../storybook/test-util';
import {Item} from '../Item/Item';

test('it handles arrow navigation', () => {
  jest.useFakeTimers();

  render(
    <ItemCollection>
      <Item>First item</Item>
      <Item>Second item</Item>
      An invalid element
    </ItemCollection>
  );

  jest.runAllTimers();

  expect(screen.getByText('First item').parentNode).toHaveFocus();
  fireEvent.keyDown(screen.getByText('First item'), {key: 'ArrowDown', code: 'ArrowDown'});
  expect(screen.getByText('Second item').parentNode).toHaveFocus();
  fireEvent.keyDown(screen.getByText('Second item'), {key: 'ArrowUp', code: 'ArrowUp'});
  expect(screen.getByText('First item').parentNode).toHaveFocus();
});
