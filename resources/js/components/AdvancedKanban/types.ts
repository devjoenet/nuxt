export interface AdvancedKanbanCard {
  description?: string;
  id: string;
  title: string;
}

export interface AdvancedKanbanColumn {
  cards: AdvancedKanbanCard[];
  id: string;
  title: string;
}

export interface AdvancedKanbanMove {
  cardId: string;
  fromColumnId: string;
  toColumnId: string;
  toIndex: number;
}
