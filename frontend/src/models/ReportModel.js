import { downloadFile } from './ApiClient.js';

function qs(params) {
  const search = new URLSearchParams(params);
  const s = search.toString();
  return s ? `?${s}` : '';
}

export const ReportModel = {
  downloadReportCard(studentId) {
    return downloadFile(`/reports/report-card/pdf${qs({ student_id: studentId })}`, 'report-card.pdf');
  },
  downloadAttendanceSheet(schoolClassId, from, to) {
    return downloadFile(
      `/reports/attendance-sheet/pdf${qs({ school_class_id: schoolClassId, from, to })}`,
      'attendance-sheet.pdf',
    );
  },
  downloadFeeReceipt(studentFeeId) {
    return downloadFile(`/reports/fee-receipt/pdf${qs({ student_fee_id: studentFeeId })}`, 'fee-receipt.pdf');
  },
  exportStudents() {
    return downloadFile('/exports/students', 'students.csv');
  },
  exportAttendance(filters = {}) {
    return downloadFile(`/exports/attendance${qs(filters)}`, 'attendance.csv');
  },
  exportFees() {
    return downloadFile('/exports/fees', 'student-fees.csv');
  },
};
