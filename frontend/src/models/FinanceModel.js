import { apiRequest } from './ApiClient.js';

export const FinanceModel = {
  listPaymentOrders(page = 1) {
    return apiRequest(`/finance/payment-orders?page=${page}`);
  },
  expenseSummary() {
    return apiRequest('/finance/expenses/summary');
  },
  listExpenses(page = 1) {
    return apiRequest(`/finance/expenses?page=${page}`);
  },
  createExpense(data) {
    return apiRequest('/finance/expenses', { method: 'POST', body: JSON.stringify(data) });
  },
  deleteExpense(id) {
    return apiRequest(`/finance/expenses/${id}`, { method: 'DELETE' });
  },
};
